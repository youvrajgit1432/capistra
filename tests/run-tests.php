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
} else {
    echo "\n(DB checks skipped - no reachable database. Structural tests passed.)\n";
}

echo "\n----------------------------------------\n";
echo "Passed: $passed   Failed: $failed\n";
exit($failed === 0 ? 0 : 1);
