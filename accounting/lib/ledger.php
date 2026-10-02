<?php
declare(strict_types=1);

/**
 * Capistra - double-entry accounting engine.
 *
 * Pure-ish functions over a PDO connection. The single most important
 * invariant: every posted journal entry must satisfy
 *     SUM(debit) == SUM(credit)
 * `ledger_post_entry()` enforces this and rejects imbalanced entries.
 */

if (!function_exists('ledger_validate_balance')) {
    /**
     * Validate that a set of journal lines is a legal double-entry posting.
     * Pure function (no database) so it can be unit-tested in isolation.
     *
     * @param list<array{account_id?:int,debit?:float|int|string,credit?:float|int|string}> $lines
     * @throws InvalidArgumentException
     */
    function ledger_validate_balance(array $lines): void
    {
        if (count($lines) < 2) {
            throw new InvalidArgumentException('A journal entry needs at least two lines.');
        }

        $totalDebit  = 0.0;
        $totalCredit = 0.0;
        $nonZeroLines = 0;
        foreach ($lines as $line) {
            $d = (float) ($line['debit'] ?? 0);
            $c = (float) ($line['credit'] ?? 0);
            if ($d > 0 && $c > 0) {
                throw new InvalidArgumentException('A journal line cannot have both a debit and a credit.');
            }
            if ($d != 0.0 || $c != 0.0) {
                $nonZeroLines++;
            }
            $totalDebit  += $d;
            $totalCredit += $c;
        }

        if ($nonZeroLines < 2) {
            throw new InvalidArgumentException('A journal entry needs at least two non-zero lines.');
        }
        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new InvalidArgumentException(sprintf(
                'Unbalanced journal entry: debit %.2f != credit %.2f',
                $totalDebit,
                $totalCredit
            ));
        }
        if (round($totalDebit, 2) <= 0.0) {
            throw new InvalidArgumentException('Journal entry total must be greater than zero.');
        }
    }
}

if (!function_exists('ledger_post_entry')) {
    /**
     * Post a balanced journal entry.
     *
     * @param array{entry_date:string,reference?:?string,memo?:?string,source_type?:string,source_id?:?int,created_by?:?int} $entry
     * @param list<array{account_id:int,debit:float|int|string,credit:float|int|string,line_memo?:?string}> $lines
     * @return int The new journal entry id.
     * @throws InvalidArgumentException on an imbalanced entry or invalid lines.
     */
    function ledger_post_entry(PDO $pdo, array $entry, array $lines): int
    {
        ledger_validate_balance($lines);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO journal_entries (entry_date, reference, memo, source_type, source_id, status, created_by)
                 VALUES (:d, :ref, :memo, :stype, :sid, :status, :by)'
            );
            $stmt->execute([
                ':d'      => $entry['entry_date'],
                ':ref'    => $entry['reference'] ?? null,
                ':memo'   => $entry['memo'] ?? null,
                ':stype'  => $entry['source_type'] ?? 'manual',
                ':sid'    => $entry['source_id'] ?? null,
                ':status' => $entry['status'] ?? 'posted',
                ':by'     => $entry['created_by'] ?? null,
            ]);
            $entryId = (int) $pdo->lastInsertId();

            $lineStmt = $pdo->prepare(
                'INSERT INTO journal_lines (journal_entry_id, account_id, debit, credit, line_memo)
                 VALUES (:eid, :aid, :debit, :credit, :memo)'
            );
            foreach ($lines as $line) {
                $lineStmt->execute([
                    ':eid'    => $entryId,
                    ':aid'    => (int) $line['account_id'],
                    ':debit'  => round((float) ($line['debit'] ?? 0), 2),
                    ':credit' => round((float) ($line['credit'] ?? 0), 2),
                    ':memo'   => $line['line_memo'] ?? null,
                ]);
            }

            $pdo->commit();
            return $entryId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

if (!function_exists('ledger_account_balances')) {
    /**
     * Signed balances per account as of a date.
     * Positive = debit-heavy, negative = credit-heavy.
     *
     * @return array<int, array{code:string,name:string,type:string,balance:float}>
     */
    function ledger_account_balances(PDO $pdo, ?string $asOf = null): array
    {
        // Aggregate per-account movement in a subquery so accounts with no
        // lines are still returned, then apply the optional as-of date filter.
        $params = [];
        $asOfClause = '';
        if ($asOf !== null) {
            $asOfClause = ' AND e.entry_date <= :asof';
            $params[':asof'] = $asOf;
        }
        $sql = 'SELECT a.id, a.code, a.name, a.type, a.opening_balance, a.opening_balance_type,
                       COALESCE(s.sum_debit, 0)  AS sum_debit,
                       COALESCE(s.sum_credit, 0) AS sum_credit
                FROM accounts a
                LEFT JOIN (
                    SELECT l.account_id, SUM(l.debit) AS sum_debit, SUM(l.credit) AS sum_credit
                    FROM journal_lines l
                    JOIN journal_entries e ON e.id = l.journal_entry_id
                    WHERE e.status = \'posted\'' . $asOfClause . '
                    GROUP BY l.account_id
                ) s ON s.account_id = a.id
                ORDER BY a.code';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $opening = (float) $row['opening_balance'];
            $signed  = $row['opening_balance_type'] === 'credit' ? -$opening : $opening;
            $signed += (float) $row['sum_debit'] - (float) $row['sum_credit'];
            $result[(int) $row['id']] = [
                'code'    => (string) $row['code'],
                'name'    => (string) $row['name'],
                'type'    => (string) $row['type'],
                'balance' => round($signed, 2),
            ];
        }

        return $result;
    }
}

if (!function_exists('ledger_trial_balance')) {
    /**
     * Trial balance: each account's net placed on the debit or credit side.
     *
     * @return array{rows:list<array<string,mixed>>,total_debit:float,total_credit:float,balanced:bool}
     */
    function ledger_trial_balance(PDO $pdo, ?string $asOf = null): array
    {
        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach (ledger_account_balances($pdo, $asOf) as $acc) {
            $net = $acc['balance'];
            $debit = $net > 0 ? $net : 0.0;
            $credit = $net < 0 ? -$net : 0.0;
            $totalDebit += $debit;
            $totalCredit += $credit;
            $rows[] = ['code' => $acc['code'], 'name' => $acc['name'], 'type' => $acc['type'], 'debit' => $debit, 'credit' => $credit];
        }

        return [
            'rows'         => $rows,
            'total_debit'  => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'balanced'     => round($totalDebit, 2) === round($totalCredit, 2),
        ];
    }
}

if (!function_exists('ledger_profit_loss')) {
    /**
     * Profit & Loss for a period. Income is credit-normal, expenses debit-normal.
     *
     * @return array{income:list<array<string,mixed>>,expense:list<array<string,mixed>>,total_income:float,total_expense:float,net:float}
     */
    function ledger_profit_loss(PDO $pdo, string $from, string $to): array
    {
        $balances = ledger_account_balances($pdo, $to);
        $income = [];
        $expense = [];
        $totalIncome = 0.0;
        $totalExpense = 0.0;

        // Period movement (excluding opening balances which are not period income).
        $stmt = $pdo->prepare(
            'SELECT a.id, a.code, a.name, a.type,
                    COALESCE(SUM(l.debit),0) AS d, COALESCE(SUM(l.credit),0) AS c
             FROM accounts a
             JOIN journal_lines l ON l.account_id = a.id
             JOIN journal_entries e ON e.id = l.journal_entry_id
             WHERE e.status = \'posted\' AND e.entry_date BETWEEN :from AND :to
               AND a.type IN (\'income\',\'expense\')
             GROUP BY a.id, a.code, a.name, a.type
             ORDER BY a.code'
        );
        $stmt->execute([':from' => $from, ':to' => $to]);

        foreach ($stmt->fetchAll() as $row) {
            $creditNet = (float) $row['c'] - (float) $row['d'];
            $debitNet  = (float) $row['d'] - (float) $row['c'];
            if ($row['type'] === 'income') {
                $income[] = ['code' => $row['code'], 'name' => $row['name'], 'amount' => round($creditNet, 2)];
                $totalIncome += $creditNet;
            } else {
                $expense[] = ['code' => $row['code'], 'name' => $row['name'], 'amount' => round($debitNet, 2)];
                $totalExpense += $debitNet;
            }
        }

        return [
            'income'        => $income,
            'expense'       => $expense,
            'total_income'  => round($totalIncome, 2),
            'total_expense' => round($totalExpense, 2),
            'net'           => round($totalIncome - $totalExpense, 2),
        ];
    }
}

if (!function_exists('ledger_balance_sheet')) {
    /**
     * Balance sheet as of a date. Includes current-period net result in equity
     * so the fundamental identity Assets = Liabilities + Equity holds even when
     * income/expense accounts are not yet closed to retained earnings.
     *
     * @return array<string,mixed>
     */
    function ledger_balance_sheet(PDO $pdo, string $asOf): array
    {
        $balances = ledger_account_balances($pdo, $asOf);
        $assets = [];
        $liabilities = [];
        $equity = [];
        $totalAssets = 0.0;
        $totalLiabilities = 0.0;
        $totalEquity = 0.0;
        $netIncome = 0.0;

        foreach ($balances as $acc) {
            switch ($acc['type']) {
                case 'asset':
                    $assets[] = $acc;
                    $totalAssets += $acc['balance'];
                    break;
                case 'liability':
                    $liabilities[] = ['code' => $acc['code'], 'name' => $acc['name'], 'amount' => -$acc['balance']];
                    $totalLiabilities += -$acc['balance'];
                    break;
                case 'equity':
                    $equity[] = ['code' => $acc['code'], 'name' => $acc['name'], 'amount' => -$acc['balance']];
                    $totalEquity += -$acc['balance'];
                    break;
                case 'income':
                    $netIncome += -$acc['balance'];
                    break;
                case 'expense':
                    $netIncome -= $acc['balance'];
                    break;
            }
        }

        $netIncome = round($netIncome, 2);
        if (abs($netIncome) > 0.001) {
            $equity[] = ['code' => '', 'name' => 'Current period result', 'amount' => $netIncome];
        }

        $rightTotal = round($totalLiabilities + $totalEquity + $netIncome, 2);

        return [
            'assets'            => $assets,
            'liabilities'       => $liabilities,
            'equity'            => $equity,
            'total_assets'      => round($totalAssets, 2),
            'total_liabilities' => round($totalLiabilities, 2),
            'total_equity'      => round($totalEquity, 2),
            'net_income'        => $netIncome,
            'total_liabilities_and_equity' => $rightTotal,
            'balanced'          => round($totalAssets, 2) === $rightTotal,
        ];
    }
}

if (!function_exists('ledger_general_ledger')) {
    /**
     * General ledger detail for one account.
     *
     * @return list<array<string,mixed>>
     */
    function ledger_general_ledger(PDO $pdo, int $accountId, ?string $from = null, ?string $to = null): array
    {
        $sql = 'SELECT e.id AS entry_id, e.entry_date, e.reference, e.memo, e.source_type,
                       l.debit, l.credit, l.line_memo
                FROM journal_lines l
                JOIN journal_entries e ON e.id = l.journal_entry_id
                WHERE l.account_id = :aid AND e.status = \'posted\'';
        $params = [':aid' => $accountId];
        if ($from !== null) { $sql .= ' AND e.entry_date >= :from'; $params[':from'] = $from; }
        if ($to !== null)   { $sql .= ' AND e.entry_date <= :to';   $params[':to']   = $to; }
        $sql .= ' ORDER BY e.entry_date, e.id';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $rows = [];
        $running = 0.0;
        foreach ($stmt->fetchAll() as $row) {
            $running += (float) $row['debit'] - (float) $row['credit'];
            $row['running'] = round($running, 2);
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('ledger_cash_flow')) {
    /**
     * Simplified cash-flow summary derived from journal entries that touch a
     * cash account, classified by their source type. Labeled "approximate" in
     * the UI. This is a management summary, not a statutory cash-flow statement.
     *
     * @return array{operating:float,investing:float,financing:float,net:float,rows:list<array<string,mixed>>}
     */
    function ledger_cash_flow(PDO $pdo, string $from, string $to): array
    {
        $stmt = $pdo->prepare(
            'SELECT e.source_type,
                    COALESCE(SUM(l.debit),0) - COALESCE(SUM(l.credit),0) AS net_cash
             FROM journal_lines l
             JOIN journal_entries e ON e.id = l.journal_entry_id
             JOIN accounts a ON a.id = l.account_id
             WHERE e.status = \'posted\' AND a.is_cash_account = 1
               AND e.entry_date BETWEEN :from AND :to
             GROUP BY e.source_type'
        );
        $stmt->execute([':from' => $from, ':to' => $to]);

        $operating = 0.0;
        $investing = 0.0;
        $financing = 0.0;
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $net = (float) $row['net_cash'];
            $type = (string) $row['source_type'];
            $bucket = match ($type) {
                'income', 'expense', 'billing' => 'operating',
                'investment'                   => 'investing',
                'opening'                      => 'financing',
                default                        => 'operating',
            };
            if ($bucket === 'operating') { $operating += $net; }
            elseif ($bucket === 'investing') { $investing += $net; }
            else { $financing += $net; }
            $rows[] = ['source_type' => $type, 'bucket' => $bucket, 'net' => round($net, 2)];
        }

        return [
            'operating' => round($operating, 2),
            'investing' => round($investing, 2),
            'financing' => round($financing, 2),
            'net'       => round($operating + $investing + $financing, 2),
            'rows'      => $rows,
        ];
    }
}
