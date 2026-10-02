<?php
declare(strict_types=1);

/**
 * Capistra - integration between income/expense workflows and the ledger.
 *
 * Posting is now DATA-DRIVEN:
 *   * the category determines the income/expense account
 *     (`transaction_categories.ledger_account_id`), and
 *   * the selected financial account determines the cash/bank side
 *     (`financial_accounts.ledger_account_id`), falling back to the configured
 *     cash account when no financial account is selected.
 *
 * Integration stays idempotent: a row is only posted once, and the resulting
 * journal_entry_id is written back, so it is safe to run repeatedly.
 */

require_once __DIR__ . '/ledger.php';
require_once dirname(__DIR__, 2) . '/lib/categories.php';
require_once dirname(__DIR__, 2) . '/lib/financial_accounts.php';
require_once dirname(__DIR__, 2) . '/lib/periods.php';

if (!function_exists('capistra_income_account_code')) {
    /** @deprecated kept for legacy callers; category mapping is now data-driven. */
    function capistra_income_account_code(string $category): string
    {
        return '4000';
    }
}

if (!function_exists('capistra_expense_account_code')) {
    /** @deprecated kept for legacy callers; category mapping is now data-driven. */
    function capistra_expense_account_code(string $category): string
    {
        return '5900';
    }
}

if (!function_exists('capistra_cash_account_id')) {
    /** The configured cash/bank ledger account used as a fallback. */
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

if (!function_exists('capistra_money_account_for')) {
    /**
     * Resolve the ledger account that represents the money side of a
     * transaction: the selected financial account's ledger account, else cash.
     */
    function capistra_money_account_for(PDO $pdo, ?int $financialAccountId): int
    {
        if ($financialAccountId !== null && $financialAccountId > 0) {
            $account = capistra_financial_account_get($financialAccountId);
            if ($account && !empty($account['ledger_account_id'])) {
                return (int) $account['ledger_account_id'];
            }
        }
        return capistra_cash_account_id($pdo);
    }
}

if (!function_exists('capistra_post_income')) {
    /**
     * Post a single income row to the ledger if it has not been posted yet.
     * Returns the journal entry id, or null if nothing to do.
     */
    function capistra_post_income(PDO $pdo, int $incomeId): ?int
    {
        $stmt = $pdo->prepare('SELECT id, category, date, amount, remarks, journal_entry_id, financial_account_id
                               FROM income WHERE id = ? LIMIT 1');
        $stmt->execute([$incomeId]);
        $row = $stmt->fetch();
        if (!$row || !empty($row['journal_entry_id'])) {
            return null;
        }

        capistra_assert_period_open((string) $row['date']);
        $money   = capistra_money_account_for($pdo, isset($row['financial_account_id']) ? (int) $row['financial_account_id'] : null);
        $incomeA = capistra_category_resolve_ledger((string) $row['category'], 'income');
        $amount  = round((float) $row['amount'], 2);

        $entryId = ledger_post_entry($pdo, [
            'entry_date'  => (string) $row['date'],
            'reference'   => 'INC-' . $incomeId,
            'memo'        => 'Income: ' . (string) $row['category'],
            'source_type' => 'income',
            'source_id'   => $incomeId,
        ], [
            ['account_id' => $money,   'debit'  => $amount, 'credit' => 0,       'line_memo' => 'Cash received'],
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
        $stmt = $pdo->prepare('SELECT id, category, date, amount, remarks, journal_entry_id, financial_account_id
                               FROM expenses WHERE id = ? LIMIT 1');
        $stmt->execute([$expenseId]);
        $row = $stmt->fetch();
        if (!$row || !empty($row['journal_entry_id'])) {
            return null;
        }

        capistra_assert_period_open((string) $row['date']);
        $money    = capistra_money_account_for($pdo, isset($row['financial_account_id']) ? (int) $row['financial_account_id'] : null);
        $expenseA = capistra_category_resolve_ledger((string) $row['category'], 'expense');
        $amount   = round((float) $row['amount'], 2);

        $entryId = ledger_post_entry($pdo, [
            'entry_date'  => (string) $row['date'],
            'reference'   => 'EXP-' . $expenseId,
            'memo'        => 'Expense: ' . (string) $row['category'],
            'source_type' => 'expense',
            'source_id'   => $expenseId,
        ], [
            ['account_id' => $expenseA, 'debit'  => $amount, 'credit' => 0,       'line_memo' => (string) $row['category']],
            ['account_id' => $money,    'debit'  => 0,       'credit' => $amount, 'line_memo' => 'Cash paid'],
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
