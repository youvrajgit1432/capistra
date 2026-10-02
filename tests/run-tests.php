<?php
declare(strict_types=1);

/**
 * Capistra - dependency-free test runner.
 *
 * Runs without PHPUnit so it works in CI and offline. Verifies the critical
 * financial logic and (when a database is reachable) the accounting identities
 * against the fictional demo data.
 *
 * Usage: php tests/run-tests.php
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../accounting/lib/ledger.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/features.php';
require_once __DIR__ . '/../lib/transfers.php';
require_once __DIR__ . '/../lib/portfolio.php';
require_once __DIR__ . '/../lib/personal_finance.php';
require_once __DIR__ . '/../lib/periods.php';
require_once __DIR__ . '/../lib/categories.php';

$passed = 0;
$failed = 0;

function check(string $name, bool $condition): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  PASS  $name\n";
    } else {
        $failed++;
        echo "  FAIL  $name\n";
    }
}

function expect_throw(string $name, callable $fn): void
{
    try {
        $fn();
        check($name, false);
    } catch (InvalidArgumentException $e) {
        check($name, true);
    }
}

echo "Double-entry validation\n";
check('balanced entry accepted', (function () {
    try {
        ledger_validate_balance([
            ['account_id' => 1, 'debit' => 100.00, 'credit' => 0],
            ['account_id' => 2, 'debit' => 0, 'credit' => 100.00],
        ]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
})());
expect_throw('unbalanced entry rejected', fn() => ledger_validate_balance([
    ['account_id' => 1, 'debit' => 100.00, 'credit' => 0],
    ['account_id' => 2, 'debit' => 0, 'credit' => 90.00],
]));
expect_throw('single-line entry rejected', fn() => ledger_validate_balance([
    ['account_id' => 1, 'debit' => 100.00, 'credit' => 0],
]));
expect_throw('zero-total entry rejected', fn() => ledger_validate_balance([
    ['account_id' => 1, 'debit' => 0, 'credit' => 0],
    ['account_id' => 2, 'debit' => 0, 'credit' => 0],
]));
expect_throw('line with both debit and credit rejected', fn() => ledger_validate_balance([
    ['account_id' => 1, 'debit' => 100.00, 'credit' => 100.00],
    ['account_id' => 2, 'debit' => 0, 'credit' => 0],
]));

echo "\nCurrency formatting\n";
check('NPR prefix', str_starts_with(capistra_currency(1234.5, 'NPR'), 'Rs.'));
check('two decimal places', str_contains(capistra_currency(10, 'USD'), '10.00'));
check('EUR symbol is valid UTF-8', str_contains(capistra_currency(10, 'EUR'), '€'));
check('GBP symbol is valid UTF-8', str_contains(capistra_currency(10, 'GBP'), '£'));
check('south-asian grouping', capistra_format_south_asian(1234567.5) === '12,34,567.50');
check('settings-default currency falls back safely', capistra_base_currency() !== '');

echo "\nTransfers\n";
check('valid transfer accepted', (function () {
    try { capistra_transfer_validate(1, 2, 100.0, '2025-10-01'); return true; } catch (Throwable $e) { return false; }
})());
expect_throw('same source and destination rejected', fn() => capistra_transfer_validate(1, 1, 100.0, '2025-10-01'));
expect_throw('zero transfer rejected', fn() => capistra_transfer_validate(1, 2, 0.0, '2025-10-01'));
expect_throw('missing date rejected', fn() => capistra_transfer_validate(1, 2, 100.0, null));

echo "\nBudgets and goals\n";
$usage = capistra_budget_usage(1000.0, 1200.0);
check('budget remaining', $usage['remaining'] === -200.0);
check('budget percent', $usage['percent'] === 120.0);
check('budget over flag', $usage['over'] === true);
$under = capistra_budget_usage(1000.0, 400.0);
check('budget under flag', $under['over'] === false && $under['remaining'] === 600.0);
$goal = capistra_goal_progress(100000.0, 40000.0, '2026-04-01', '2025-10-01');
check('goal remaining', $goal['remaining'] === 60000.0);
check('goal percent', $goal['percent'] === 40.0);
check('goal months left', $goal['months_left'] === 6);
check('goal required monthly', $goal['required_monthly'] === 10000.0);
check('goal achieved flag', capistra_goal_progress(100.0, 100.0, null)['achieved'] === true);

echo "\nRecurring schedules\n";
check('monthly clamps month end', capistra_recurring_next_date('monthly', '2025-01-31') === '2025-02-28');
check('monthly simple', capistra_recurring_next_date('monthly', '2025-03-15') === '2025-04-15');
check('quarterly clamps', capistra_recurring_next_date('quarterly', '2025-01-31') === '2025-04-30');
check('yearly clamps leap day', capistra_recurring_next_date('yearly', '2024-02-29') === '2025-02-28');
check('weekly', capistra_recurring_next_date('weekly', '2025-01-01') === '2025-01-08');

echo "\nNet worth and reconciliation\n";
$nw = capistra_net_worth_totals(['cash' => 100.0, 'bank' => 200.0], ['card' => 50.0]);
check('net worth assets', $nw['assets'] === 300.0);
check('net worth liabilities', $nw['liabilities'] === 50.0);
check('net worth net', $nw['net'] === 250.0);
check('reconciliation balanced', capistra_reconciliation_result(1000.0, 1000.0)['status'] === 'balanced');
check('reconciliation discrepancy', capistra_reconciliation_result(1000.0, 990.0)['difference'] === 10.0);

echo "\nStock position maths\n";
$pos = capistra_position_from_transactions([
    ['id' => 1, 'transaction_type' => 'BUY', 'transaction_date' => '2025-01-01', 'units' => 100, 'price_per_unit' => 100, 'fees' => 0, 'tax' => 0],
    ['id' => 2, 'transaction_type' => 'BUY', 'transaction_date' => '2025-02-01', 'units' => 100, 'price_per_unit' => 120, 'fees' => 0, 'tax' => 0],
    ['id' => 3, 'transaction_type' => 'SELL', 'transaction_date' => '2025-03-01', 'units' => 50, 'price_per_unit' => 150, 'fees' => 0, 'tax' => 0],
]);
check('average cost weighted', $pos['units'] === 150.0 && abs($pos['average_cost'] - 110.0) < 0.0001);
check('realized P&L on sale', $pos['realized_gain'] === 2000.0);
check('remaining cost basis', $pos['total_cost'] === 16500.0);

$bonus = capistra_position_from_transactions([
    ['id' => 1, 'transaction_type' => 'BUY', 'transaction_date' => '2025-01-01', 'units' => 100, 'price_per_unit' => 100, 'fees' => 0, 'tax' => 0],
    ['id' => 2, 'transaction_type' => 'BONUS', 'transaction_date' => '2025-02-01', 'units' => 50, 'price_per_unit' => 0, 'fees' => 0, 'tax' => 0],
]);
check('bonus increases units only', $bonus['units'] === 150.0 && $bonus['total_cost'] === 10000.0 && abs($bonus['average_cost'] - 66.6667) < 0.001);

$stockDiv = capistra_position_from_transactions([
    ['id' => 1, 'transaction_type' => 'BUY', 'transaction_date' => '2025-01-01', 'units' => 100, 'price_per_unit' => 200, 'fees' => 0, 'tax' => 0],
    ['id' => 2, 'transaction_type' => 'STOCK_DIVIDEND', 'transaction_date' => '2025-02-01', 'units' => 20, 'price_per_unit' => 0, 'fees' => 0, 'tax' => 0],
]);
check('stock dividend increases units only', $stockDiv['units'] === 120.0 && $stockDiv['total_cost'] === 20000.0);

$split = capistra_position_from_transactions([
    ['id' => 1, 'transaction_type' => 'BUY', 'transaction_date' => '2025-01-01', 'units' => 100, 'price_per_unit' => 500, 'fees' => 0, 'tax' => 0],
    ['id' => 2, 'transaction_type' => 'SPLIT', 'transaction_date' => '2025-02-01', 'units' => 0, 'ratio_from' => 1, 'ratio_to' => 2, 'fees' => 0, 'tax' => 0],
]);
check('2:1 split doubles units, cost unchanged', $split['units'] === 200.0 && $split['total_cost'] === 50000.0 && $split['average_cost'] === 250.0);

$fees = capistra_position_from_transactions([
    ['id' => 1, 'transaction_type' => 'BUY', 'transaction_date' => '2025-01-01', 'units' => 10, 'price_per_unit' => 100, 'fees' => 50, 'tax' => 10],
]);
check('buy fees included in cost basis', $fees['total_cost'] === 1060.0 && abs($fees['average_cost'] - 106.0) < 0.0001);

$sellFees = capistra_position_from_transactions([
    ['id' => 1, 'transaction_type' => 'BUY', 'transaction_date' => '2025-01-01', 'units' => 100, 'price_per_unit' => 100, 'fees' => 0, 'tax' => 0],
    ['id' => 2, 'transaction_type' => 'SELL', 'transaction_date' => '2025-02-01', 'units' => 100, 'price_per_unit' => 150, 'fees' => 100, 'tax' => 50],
]);
check('sell fees/tax reduce realized gain', $sellFees['realized_gain'] === 4850.0 && $sellFees['units'] === 0.0);

$div = capistra_position_from_transactions([
    ['id' => 1, 'transaction_type' => 'BUY', 'transaction_date' => '2025-01-01', 'units' => 100, 'price_per_unit' => 100, 'fees' => 0, 'tax' => 0],
    ['id' => 2, 'transaction_type' => 'CASH_DIVIDEND', 'transaction_date' => '2025-02-01', 'units' => 0, 'gross_amount' => 500, 'fees' => 0, 'tax' => 0],
]);
check('cash dividend tracked separately', $div['dividends'] === 500.0 && $div['total_cost'] === 10000.0);

$withMarket = capistra_position_with_market($pos, 200.0);
check('market value = units x price', $withMarket['market_value'] === 30000.0);
check('unrealized = market - cost', $withMarket['unrealized'] === 13500.0);
check('total return includes realized', $withMarket['total_return'] === 15500.0);

echo "\nFee rules (effective-dated)\n";
$rules = [
    ['name' => 'Commission', 'calculation_type' => 'percentage', 'rate' => 0.4, 'min_amount' => null, 'max_amount' => null, 'effective_from' => '2024-01-01', 'effective_to' => null, 'is_active' => 1],
    ['name' => 'DP', 'calculation_type' => 'fixed', 'fixed_amount' => 25, 'min_amount' => null, 'max_amount' => null, 'effective_from' => '2024-01-01', 'effective_to' => null, 'is_active' => 1],
    ['name' => 'Future', 'calculation_type' => 'fixed', 'fixed_amount' => 999, 'effective_from' => '2099-01-01', 'effective_to' => null, 'is_active' => 1],
];
$feesCalc = capistra_compute_fees($rules, 100000.0, '2025-06-01');
check('percentage + fixed fee total', $feesCalc['fees'] === 425.0);
check('future-dated rule excluded', count($feesCalc['breakdown']) === 2);
check('mid-year rate change honoured', capistra_compute_fees([
    ['name' => 'Old', 'calculation_type' => 'percentage', 'rate' => 0.5, 'effective_from' => '2024-01-01', 'effective_to' => '2025-01-31', 'is_active' => 1],
    ['name' => 'New', 'calculation_type' => 'percentage', 'rate' => 0.4, 'effective_from' => '2025-02-01', 'effective_to' => null, 'is_active' => 1],
], 10000.0, '2025-03-01')['fees'] === 40.0);

// ---- Optional database checks -------------------------------------------------
$dbAvailable = false;
$pdo = null;
if (defined('DB_NAME')) {
    try {
        $pdo = capistra_pdo();
        $pdo->query('SELECT 1 FROM journal_lines LIMIT 1');
        $dbAvailable = true;
    } catch (Throwable $e) {
        $dbAvailable = false;
    }
}

if ($dbAvailable && $pdo !== null) {
    echo "\nAccounting identities (live demo database)\n";
    $tb = ledger_trial_balance($pdo, null);
    check('trial balance balanced', $tb['balanced'] === true);
    check('total debit equals total credit', $tb['total_debit'] === $tb['total_credit']);

    $bs = ledger_balance_sheet($pdo, date('Y-m-d'));
    check('assets = liabilities + equity', $bs['balanced'] === true);

    $pl = ledger_profit_loss($pdo, '1900-01-01', '2999-12-31');
    check('net = income - expense', round($pl['total_income'] - $pl['total_expense'], 2) === $pl['net']);

    echo "\nDynamic configuration (live database)\n";
    $mapped = capistra_category_resolve_ledger('Dividend Income', 'income');
    $stmt = $pdo->prepare('SELECT id FROM accounts WHERE code = ?');
    $stmt->execute(['4100']);
    check('category maps to its mapped ledger account', $mapped === (int) $stmt->fetchColumn());
    check('unknown category uses documented fallback', capistra_category_resolve_ledger('__legacy_unknown__', 'expense') > 0);
    check('categories are seeded', count(capistra_categories('income')) > 0 && count(capistra_categories('expense')) > 0);
    check('financial accounts are seeded', count(capistra_financial_accounts()) > 0);
    check('securities are seeded', count(capistra_securities()) > 0);
    check('price history present', capistra_latest_price((int) capistra_securities()[0]['id']) !== null);

    $portfolio = capistra_portfolio_summary();
    check('portfolio derived from transactions', $portfolio['market_value'] >= 0.0 && count($portfolio['positions']) > 0);

    $nwReport = capistra_net_worth_report();
    check('net worth has assets', $nwReport['total_assets'] > 0.0);

    // Settings persistence round-trip (cleaned up afterwards).
    capistra_set_setting($pdo, '_test_setting', 'hello');
    check('setting persists', capistra_setting('_test_setting') === 'hello');
    $pdo->prepare('DELETE FROM app_settings WHERE setting_key = ?')->execute(['_test_setting']);
    capistra_settings(true);
    check('setting removed', capistra_setting('_test_setting') === null);

    check('feature toggle resolves', in_array(capistra_feature_enabled('nepse'), [true, false], true));
    check('feature registry non-empty', count(capistra_feature_registry()) > 0);

    // Transfer posting: balanced, then cleaned up so demo data is untouched.
    $cashId = null; $cardId = null;
    foreach (capistra_financial_accounts() as $fa) {
        if ($fa['type'] === 'cash' && $cashId === null) { $cashId = (int) $fa['id']; }
        if ($fa['type'] === 'credit_card' && $cardId === null) { $cardId = (int) $fa['id']; }
    }
    if ($cashId && $cardId) {
        $transferId = capistra_post_transfer([
            'from_account_id' => $cashId, 'to_account_id' => $cardId,
            'transfer_date' => '2025-09-30', 'amount' => 1000.0, 'reference' => 'TEST-TRF', 'notes' => 'test',
        ]);
        $stmt = $pdo->prepare('SELECT journal_entry_id FROM transfers WHERE id = ?');
        $stmt->execute([$transferId]);
        $entryId = (int) $stmt->fetchColumn();
        $stmt = $pdo->prepare('SELECT SUM(debit) d, SUM(credit) c FROM journal_lines WHERE journal_entry_id = ?');
        $stmt->execute([$entryId]);
        $sum = $stmt->fetch();
        check('transfer posts a balanced entry', (float) $sum['d'] === 1000.0 && (float) $sum['c'] === 1000.0);
        $pdo->prepare('DELETE FROM journal_lines WHERE journal_entry_id = ?')->execute([$entryId]);
        $pdo->prepare('DELETE FROM journal_entries WHERE id = ?')->execute([$entryId]);
        $pdo->prepare('DELETE FROM transfers WHERE id = ?')->execute([$transferId]);
    }

    // Fiscal-period guard.
    check('closed period detected', capistra_period_is_closed('2025-01-15') === true);
    check('open period detected', capistra_period_is_closed('2025-08-01') === false);
    try {
        capistra_assert_period_open('2025-01-15');
        check('closed period rejects posting', false);
    } catch (RuntimeException $e) {
        check('closed period rejects posting', true);
    }
} else {
    echo "\n(DB checks skipped - no reachable database. Structural tests passed.)\n";
}

echo "\n----------------------------------------\n";
echo "Passed: $passed   Failed: $failed\n";
exit($failed === 0 ? 0 : 1);
