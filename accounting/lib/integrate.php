<?php
declare(strict_types=1);

/**
 * Capistra - integration between the simple income/expense workflows and the
 * double-entry ledger.
 *
 * Integration is idempotent: a row is only posted once, and the resulting
 * journal_entry_id is written back. This means it is safe to run repeatedly and
 * safe for legacy save paths to call.
 */

require_once __DIR__ . '/ledger.php';

if (!function_exists('capistra_income_account_code')) {
    /** Map an income category to a chart-of-accounts code. */
    function capistra_income_account_code(string $category): string
    {
        $investment = [
            'Investment', 'Investment Returns', 'Stock Market Profits', 'Real Estate Income',
            'Dividend Income', 'Loan Interest', 'Interest from Fixed Deposits',
            'Trading Profits', 'Portfolio Management Fees', 'Fund Management Fees',
        ];
        foreach ($investment as $needle) {
            if (strcasecmp($category, $needle) === 0) {
                return '4100'; // Investment Returns
            }
        }
        return '4000'; // Operating Income
    }
}

if (!function_exists('capistra_expense_account_code')) {
    /** Map an expense category to a chart-of-accounts code. */
    function capistra_expense_account_code(string $category): string
    {
        $map = [
            'Office Rent' => '5000',
            'Employee Salaries' => '5100',
            'Salaries and Wages' => '5100',
            'Employee Benefits' => '5100',
            'Utilities' => '5200',
            'Marketing and Advertising' => '5300',
            'Marketing' => '5300',
            'Professional Services' => '5400',
            'Consultancy Fees' => '5400',
            'Legal' => '5400',
        ];
        return $map[$category] ?? '5900'; // Other Expenses
    }
}

if (!function_exists('capistra_cash_account_id')) {
    function capistra_cash_account_id(PDO $pdo): int
    {
        $id = $pdo->query("SELECT id FROM accounts WHERE is_cash_account = 1 ORDER BY code LIMIT 1")->fetchColumn();
        if ($id === false) {
            throw new RuntimeException('No cash account defined in the chart of accounts.');
        }
        return (int) $id;
    }
}

if (!function_exists('capistra_account_id_by_code')) {
    function capistra_account_id_by_code(PDO $pdo, string $code): int
    {
        $stmt = $pdo->prepare('SELECT id FROM accounts WHERE code = ? LIMIT 1');
        $stmt->execute([$code]);
        $id = $stmt->fetchColumn();
        if ($id === false) {
            throw new RuntimeException("Chart of accounts is missing account code $code.");
        }
        return (int) $id;
    }
}

if (!function_exists('capistra_post_income')) {
    /**
     * Post a single income row to the ledger if it has not been posted yet.
     * Returns the journal entry id, or null if nothing to do.
     */
    function capistra_post_income(PDO $pdo, int $incomeId): ?int
    {
        $stmt = $pdo->prepare('SELECT id, category, date, amount, remarks, journal_entry_id FROM income WHERE id = ? LIMIT 1');
        $stmt->execute([$incomeId]);
        $row = $stmt->fetch();
        if (!$row || !empty($row['journal_entry_id'])) {
            return null;
        }

        $cash    = capistra_cash_account_id($pdo);
        $incomeA = capistra_account_id_by_code($pdo, capistra_income_account_code((string) $row['category']));
        $amount  = round((float) $row['amount'], 2);

        $entryId = ledger_post_entry($pdo, [
            'entry_date'  => (string) $row['date'],
            'reference'   => 'INC-' . $incomeId,
            'memo'        => 'Income: ' . (string) $row['category'],
            'source_type' => 'income',
            'source_id'   => $incomeId,
        ], [
            ['account_id' => $cash,    'debit'  => $amount, 'credit' => 0,       'line_memo' => 'Cash received'],
            ['account_id' => $incomeA, 'debit'  => 0,       'credit' => $amount, 'line_memo' => (string) $row['category']],
        ]);

        $upd = $pdo->prepare('UPDATE income SET journal_entry_id = ? WHERE id = ?');
        $upd->execute([$entryId, $incomeId]);

        return $entryId;
    }
}

if (!function_exists('capistra_post_expense')) {
    /**
     * Post a single expense row to the ledger if it has not been posted yet.
     * Returns the journal entry id, or null if nothing to do.
     */
    function capistra_post_expense(PDO $pdo, int $expenseId): ?int
    {
        $stmt = $pdo->prepare('SELECT id, category, date, amount, remarks, journal_entry_id FROM expenses WHERE id = ? LIMIT 1');
        $stmt->execute([$expenseId]);
        $row = $stmt->fetch();
        if (!$row || !empty($row['journal_entry_id'])) {
            return null;
        }

        $cash     = capistra_cash_account_id($pdo);
        $expenseA = capistra_account_id_by_code($pdo, capistra_expense_account_code((string) $row['category']));
        $amount   = round((float) $row['amount'], 2);

        $entryId = ledger_post_entry($pdo, [
            'entry_date'  => (string) $row['date'],
            'reference'   => 'EXP-' . $expenseId,
            'memo'        => 'Expense: ' . (string) $row['category'],
            'source_type' => 'expense',
            'source_id'   => $expenseId,
        ], [
            ['account_id' => $expenseA, 'debit'  => $amount, 'credit' => 0,       'line_memo' => (string) $row['category']],
            ['account_id' => $cash,     'debit'  => 0,       'credit' => $amount, 'line_memo' => 'Cash paid'],
        ]);

        $upd = $pdo->prepare('UPDATE expenses SET journal_entry_id = ? WHERE id = ?');
        $upd->execute([$entryId, $expenseId]);

        return $entryId;
    }
}

if (!function_exists('capistra_sync_ledger')) {
    /**
     * Post every unposted income and expense row. Idempotent.
     *
     * @return array{income:int,expense:int}
     */
    function capistra_sync_ledger(PDO $pdo): array
    {
        $incomeIds  = $pdo->query('SELECT id FROM income   WHERE journal_entry_id IS NULL ORDER BY date, id')->fetchAll(PDO::FETCH_COLUMN);
        $expenseIds = $pdo->query('SELECT id FROM expenses WHERE journal_entry_id IS NULL ORDER BY date, id')->fetchAll(PDO::FETCH_COLUMN);

        $postedIncome = 0;
        $postedExpense = 0;
        foreach ($incomeIds as $id) {
            if (capistra_post_income($pdo, (int) $id) !== null) { $postedIncome++; }
        }
        foreach ($expenseIds as $id) {
            if (capistra_post_expense($pdo, (int) $id) !== null) { $postedExpense++; }
        }

        return ['income' => $postedIncome, 'expense' => $postedExpense];
    }
}
