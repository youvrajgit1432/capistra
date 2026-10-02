<?php
declare(strict_types=1);

/**
 * Capistra - safe CSV import/export.
 *
 * Imports are previewed, validated and duplicate-checked before anything is
 * committed. No direct bank credential integrations exist in this phase.
 */

require_once __DIR__ . '/nepse.php';
require_once __DIR__ . '/categories.php';
require_once __DIR__ . '/financial_accounts.php';

if (!function_exists('capistra_csv_parse')) {
    /**
     * Parse CSV text into a header + associative rows.
     *
     * @return array{header:list<string>,rows:list<array<string,string>>}
     */
    function capistra_csv_parse(string $content): array
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = array_values(array_filter(explode("\n", $content), static fn($l) => trim($l) !== ''));
        if ($lines === []) {
            return ['header' => [], 'rows' => []];
        }

        $make = static function (string $line): array {
            return array_map(static fn($v) => trim((string) $v, " \t\"'"), str_getcsv($line, ','));
        };

        $header = array_map('strtolower', $make(array_shift($lines)));
        $rows = [];
        foreach ($lines as $line) {
            $values = $make($line);
            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = $values[$i] ?? '';
            }
            $rows[] = $row;
        }
        return ['header' => $header, 'rows' => $rows];
    }
}

if (!function_exists('capistra_csv_export')) {
    /**
     * @param list<array<string,mixed>> $rows
     * @param list<string> $columns
     */
    function capistra_csv_export(array $rows, array $columns): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $columns);
        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $col) {
                $line[] = $row[$col] ?? '';
            }
            fputcsv($handle, $line);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv === false ? '' : $csv;
    }
}

if (!function_exists('capistra_csv_validate_securities')) {
    /**
     * Validate security import rows. Detects: missing fields, invalid sector,
     * invalid type, and duplicates both within the file and against the DB.
     *
     * @param list<array<string,string>> $rows
     * @return array{valid:list<array<string,string>>,errors:list<string>,duplicates:list<string>}
     */
    function capistra_csv_validate_securities(array $rows): array
    {
        $valid = []; $errors = []; $duplicates = [];
        $exchangeId = capistra_default_exchange_id();
        $sectorBySlug = [];
        foreach (capistra_sectors($exchangeId) as $s) {
            $sectorBySlug[(string) $s['slug']] = (int) $s['id'];
        }
        $seen = [];
        $existing = [];
        foreach (capistra_securities(['active_only' => false]) as $s) {
            $existing[strtoupper((string) $s['symbol'])] = true;
        }

        $types = array_keys(capistra_security_types());
        foreach ($rows as $i => $row) {
            $line = $i + 2; // header is line 1
            $symbol = strtoupper(trim((string) ($row['symbol'] ?? '')));
            $name   = trim((string) ($row['company_name'] ?? $row['name'] ?? ''));
            if ($symbol === '' || $name === '') {
                $errors[] = "Line $line: symbol and company_name are required.";
                continue;
            }
            if (isset($seen[$symbol])) {
                $duplicates[] = "Line $line: duplicate symbol $symbol within the file (first occurrence kept).";
                continue;
            }
            if (isset($existing[$symbol])) {
                $duplicates[] = "Line $line: $symbol already exists in the database (skipped).";
                continue;
            }
            $sectorSlug = capistra_slugify((string) ($row['sector'] ?? ''));
            $type = strtolower(trim((string) ($row['security_type'] ?? 'equity')));
            if ($type !== '' && !in_array($type, $types, true)) {
                $type = 'other';
            }
            $seen[$symbol] = true;
            $valid[] = [
                'symbol'        => $symbol,
                'company_name'  => $name,
                'sector_id'     => (string) ($sectorBySlug[$sectorSlug] ?? ''),
                'security_type' => $type === '' ? 'equity' : $type,
                'listing_status'=> 'unverified',
                'notes'         => 'Imported from CSV.',
            ];
        }

        return ['valid' => $valid, 'errors' => $errors, 'duplicates' => $duplicates];
    }
}

if (!function_exists('capistra_csv_import_securities')) {
    /**
     * @param list<array<string,string>> $validRows
     * @return int number imported
     */
    function capistra_csv_import_securities(array $validRows): int
    {
        $count = 0;
        foreach ($validRows as $row) {
            capistra_security_save([
                'exchange_id'   => capistra_default_exchange_id(),
                'sector_id'     => (int) ($row['sector_id'] ?? 0),
                'symbol'        => $row['symbol'],
                'company_name'  => $row['company_name'],
                'security_type' => $row['security_type'] ?? 'equity',
                'listing_status'=> 'unverified',
                'notes'         => $row['notes'] ?? null,
                'is_active'     => 1,
            ]);
            $count++;
        }
        return $count;
    }
}

if (!function_exists('capistra_csv_validate_transactions')) {
    /**
     * Validate income/expense import rows against the category master.
     *
     * @param list<array<string,string>> $rows
     * @param 'income'|'expense' $type
     * @return array{valid:list<array<string,string>>,errors:list<string>,duplicates:list<string>}
     */
    function capistra_csv_validate_transactions(array $rows, string $type): array
    {
        $pdo = capistra_pdo();
        $valid = []; $errors = []; $duplicates = [];
        $knownCategories = [];
        foreach (capistra_categories($type) as $c) {
            $knownCategories[strtolower((string) $c['name'])] = (string) $c['name'];
        }
        $accounts = [];
        foreach (capistra_financial_accounts() as $a) {
            $accounts[strtolower((string) $a['name'])] = (int) $a['id'];
        }

        foreach ($rows as $i => $row) {
            $line = $i + 2;
            $category = trim((string) ($row['category'] ?? ''));
            $date = trim((string) ($row['date'] ?? ''));
            $amount = (float) ($row['amount'] ?? 0);
            if ($category === '' || $date === '' || $amount <= 0) {
                $errors[] = "Line $line: category, date and a positive amount are required.";
                continue;
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $errors[] = "Line $line: date must be YYYY-MM-DD.";
                continue;
            }
            if (!isset($knownCategories[strtolower($category)])) {
                $errors[] = "Line $line: unknown $type category '$category'.";
                continue;
            }
            $accountName = strtolower(trim((string) ($row['account'] ?? '')));
            $accountId = $accounts[$accountName] ?? null;
            $remarks = trim((string) ($row['remarks'] ?? ''));

            // Duplicate check: same type/date/amount/remarks.
            $table = $type === 'income' ? 'income' : 'expenses';
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE date = ? AND amount = ? AND COALESCE(remarks,'') = ?");
            $stmt->execute([$date, $amount, $remarks]);
            if ((int) $stmt->fetchColumn() > 0) {
                $duplicates[] = "Line $line: matches an existing record (skipped).";
                continue;
            }

            $valid[] = [
                'category' => $knownCategories[strtolower($category)],
                'date'     => $date,
                'amount'   => (string) $amount,
                'account_id' => (string) ($accountId ?? ''),
                'remarks'  => $remarks,
            ];
        }

        return ['valid' => $valid, 'errors' => $errors, 'duplicates' => $duplicates];
    }
}

if (!function_exists('capistra_csv_import_transactions')) {
    /**
     * @param list<array<string,string>> $rows
     * @param 'income'|'expense' $type
     * @return int number imported
     */
    function capistra_csv_import_transactions(array $rows, string $type): int
    {
        $pdo = capistra_pdo();
        $table = $type === 'income' ? 'income' : 'expenses';
        $count = 0;
        foreach ($rows as $row) {
            $stmt = $pdo->prepare("INSERT INTO $table (category, date, amount, remarks, financial_account_id)
                                   VALUES (:c, :d, :a, :r, :f)");
            $stmt->execute([
                ':c' => $row['category'],
                ':d' => $row['date'],
                ':a' => (float) $row['amount'],
                ':r' => $row['remarks'] !== '' ? $row['remarks'] : null,
                ':f' => $row['account_id'] !== '' ? (int) $row['account_id'] : null,
            ]);
            $count++;
        }
        return $count;
    }
}

if (!function_exists('capistra_log_import')) {
    function capistra_log_import(string $dataset, string $fileName, int $total, int $imported, int $skipped, string $status, string $report = ''): void
    {
        capistra_pdo()->prepare('INSERT INTO import_batches
            (dataset, file_name, total_rows, imported_rows, skipped_rows, status, error_report)
            VALUES (:d, :f, :t, :i, :s, :st, :r)')
            ->execute([':d' => $dataset, ':f' => $fileName, ':t' => $total, ':i' => $imported, ':s' => $skipped, ':st' => $status, ':r' => $report]);
    }
}
