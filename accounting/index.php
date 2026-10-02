<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';

$user = capistra_require_login();
$pdo  = capistra_pdo();

$today     = date('Y-m-d');
$yearStart = date('Y-01-01');

$pl          = ledger_profit_loss($pdo, $yearStart, $today);
$bs          = ledger_balance_sheet($pdo, $today);
$cf          = ledger_cash_flow($pdo, $yearStart, $today);
$tb          = ledger_trial_balance($pdo, $today);
$balances    = ledger_account_balances($pdo, $today);

$cashPosition = 0.0;
foreach ($balances as $acc) {
    // cash accounts are identifiable via the accounts table flag
}
$cashStmt = $pdo->query('SELECT COALESCE(SUM(l.debit - l.credit),0) AS bal
                         FROM journal_lines l
                         JOIN accounts a ON a.id = l.account_id
                         JOIN journal_entries e ON e.id = l.journal_entry_id
                         WHERE a.is_cash_account = 1 AND e.status = \'posted\'');
$cashPosition = (float) $cashStmt->fetchColumn();

$inv = $pdo->query('SELECT investment_type,
                            COUNT(*) AS cnt,
                            COALESCE(SUM(invested_amount),0) AS cost,
                            COALESCE(SUM(current_value),0)   AS value
                     FROM investments WHERE status <> \'closed\'
                     GROUP BY investment_type')->fetchAll();
$invCost = 0.0; $invValue = 0.0; $invByType = [];
foreach ($inv as $row) {
    $invCost  += (float) $row['cost'];
    $invValue += (float) $row['value'];
    $invByType[(string) $row['investment_type']] = $row;
}
$unrealized = $invValue - $invCost;

$investorCapital = (float) $pdo->query('SELECT COALESCE(SUM(investment_amount),0) FROM investors WHERE deleted = 0')->fetchColumn();
$investorCount   = (int) $pdo->query('SELECT COUNT(*) FROM investors WHERE deleted = 0')->fetchColumn();
$returnsPaid     = (float) $pdo->query('SELECT COALESCE(SUM(amount),0) FROM investor_returns')->fetchColumn();
$customerCount   = (int) $pdo->query('SELECT COUNT(*) FROM customers WHERE deleted = 0')->fetchColumn();
$employeeCount   = (int) $pdo->query('SELECT COUNT(*) FROM employees WHERE employment_status = \'active\'')->fetchColumn();

$netPosition = (float) $bs['total_assets'] - (float) $bs['total_liabilities'];

capistra_layout_header('Finance Command Center', 'index.php');
?>
<div class="grid grid--kpi">
  <div class="card">
    <div class="card__label">Cash position</div>
    <div class="card__value"><?= e(capistra_currency($cashPosition)) ?></div>
    <div class="card__sub">All cash &amp; bank accounts, posted entries</div>
  </div>
  <div class="card">
    <div class="card__label">Total income (YTD)</div>
    <div class="card__value"><?= e(capistra_currency($pl['total_income'])) ?></div>
    <div class="card__sub">Since <?= e($yearStart) ?></div>
  </div>
  <div class="card">
    <div class="card__label">Total expense (YTD)</div>
    <div class="card__value"><?= e(capistra_currency($pl['total_expense'])) ?></div>
    <div class="card__sub">Since <?= e($yearStart) ?></div>
  </div>
  <div class="card">
    <div class="card__label">Net operating result</div>
    <div class="card__value"><?= capistra_amount((float) $pl['net']) ?></div>
    <div class="card__sub">Income minus expense</div>
  </div>
</div>

<div class="grid grid--kpi" style="margin-top:var(--space-xl)">
  <div class="card">
    <div class="card__label">Total assets</div>
    <div class="card__value"><?= e(capistra_currency((float) $bs['total_assets'])) ?></div>
  </div>
  <div class="card">
    <div class="card__label">Total liabilities</div>
    <div class="card__value"><?= e(capistra_currency((float) $bs['total_liabilities'])) ?></div>
  </div>
  <div class="card">
    <div class="card__label">Equity</div>
    <div class="card__value"><?= e(capistra_currency((float) $bs['total_equity'] + (float) $bs['net_income'])) ?></div>
  </div>
  <div class="card">
    <div class="card__label">Approx. net financial position</div>
    <div class="card__value"><?= capistra_amount($netPosition) ?></div>
    <div class="card__sub">Assets &minus; liabilities</div>
  </div>
</div>

<h2 style="margin-top:var(--space-3xl)">Investments &amp; capital</h2>
<div class="grid grid--2">
  <div class="card">
    <div class="card__label">Portfolio</div>
    <div class="card__value"><?= e(capistra_currency($invValue)) ?></div>
    <div class="card__sub">
      Cost basis <?= e(capistra_currency($invCost)) ?> &middot;
      Unrealized <?= capistra_amount($unrealized) ?>
    </div>
    <div class="table-wrap" style="margin-top:var(--space-lg)">
      <table class="data">
        <caption class="card__label" style="text-align:left;padding:var(--space-md) var(--space-lg)">Exposure by asset class</caption>
        <thead>
          <tr><th scope="col">Asset class</th><th scope="col" class="num">Cost</th><th scope="col" class="num">Value</th></tr>
        </thead>
        <tbody>
        <?php foreach (['stock','business','loan','real_estate'] as $type): ?>
          <tr>
            <th scope="row"><?= e(ucwords(str_replace('_', ' ', $type))) ?></th>
            <td class="num"><?= e(capistra_currency((float) ($invByType[$type]['cost'] ?? 0))) ?></td>
            <td class="num"><?= e(capistra_currency((float) ($invByType[$type]['value'] ?? 0))) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td>Total</td>
            <td class="num"><?= e(capistra_currency($invCost)) ?></td>
            <td class="num"><?= e(capistra_currency($invValue)) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card__label">Investor / fund capital</div>
    <div class="card__value"><?= e(capistra_currency($investorCapital)) ?></div>
    <div class="card__sub"><?= $investorCount ?> investors &middot; <?= e(capistra_currency($returnsPaid)) ?> returns recorded</div>

    <div class="card__label" style="margin-top:var(--space-2xl)">Cash flow (YTD, approximate)</div>
    <div class="table-wrap" style="margin-top:var(--space-md)">
      <table class="data">
        <thead><tr><th scope="col">Bucket</th><th scope="col" class="num">Net cash</th></tr></thead>
        <tbody>
          <tr><th scope="row">Operating</th><td class="num"><?= capistra_amount((float) $cf['operating']) ?></td></tr>
          <tr><th scope="row">Investing</th><td class="num"><?= capistra_amount((float) $cf['investing']) ?></td></tr>
          <tr><th scope="row">Financing</th><td class="num"><?= capistra_amount((float) $cf['financing']) ?></td></tr>
        </tbody>
        <tfoot><tr><td>Net change</td><td class="num"><?= capistra_amount((float) $cf['net']) ?></td></tr></tfoot>
      </table>
    </div>
    <p class="hint">Management summary derived from journal source types. Not a statutory cash-flow statement.</p>
  </div>
</div>

<h2 style="margin-top:var(--space-3xl)">Income vs expense (YTD) &amp; books health</h2>
<div class="grid grid--2">
  <div class="card">
    <?php
      $max = max((float) $pl['total_income'], (float) $pl['total_expense'], 1.0);
      $ip  = (int) round(((float) $pl['total_income'] / $max) * 100);
      $ep  = (int) round(((float) $pl['total_expense'] / $max) * 100);
    ?>
    <div class="card__label">Income</div>
    <div style="height:14px;background:var(--color-muted);border-radius:999px;margin:var(--space-sm) 0 var(--space-xl)">
      <div style="width:<?= $ip ?>%;height:14px;background:var(--color-positive);border-radius:999px"></div>
    </div>
    <div class="card__label">Expense</div>
    <div style="height:14px;background:var(--color-muted);border-radius:999px;margin:var(--space-sm) 0">
      <div style="width:<?= $ep ?>%;height:14px;background:var(--color-negative);border-radius:999px"></div>
    </div>
    <table class="data" style="margin-top:var(--space-xl)">
      <thead><tr><th scope="col">Metric</th><th scope="col" class="num">Amount</th></tr></thead>
      <tbody>
        <tr><th scope="row">Income</th><td class="num"><?= e(capistra_currency((float) $pl['total_income'])) ?></td></tr>
        <tr><th scope="row">Expense</th><td class="num"><?= e(capistra_currency((float) $pl['total_expense'])) ?></td></tr>
        <tr><th scope="row">Net</th><td class="num"><?= capistra_amount((float) $pl['net']) ?></td></tr>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card__label">Books health</div>
    <div class="card__value">
      <?php if ($tb['balanced']): ?>
        <span class="badge badge--pos">Trial balance balanced</span>
      <?php else: ?>
        <span class="badge badge--neg">Trial balance out of balance</span>
      <?php endif; ?>
    </div>
    <div class="card__sub">
      Debit <?= e(capistra_currency((float) $tb['total_debit'])) ?> &middot;
      Credit <?= e(capistra_currency((float) $tb['total_credit'])) ?>
    </div>
    <table class="data" style="margin-top:var(--space-xl)">
      <thead><tr><th scope="col">Identity</th><th scope="col">Status</th></tr></thead>
      <tbody>
        <tr><th scope="row">Assets = Liabilities + Equity</th>
            <td><?= $bs['balanced'] ? '<span class="badge badge--pos">OK</span>' : '<span class="badge badge--neg">Check</span>' ?></td></tr>
        <tr><th scope="row">Total debits = total credits</th>
            <td><?= $tb['balanced'] ? '<span class="badge badge--pos">OK</span>' : '<span class="badge badge--neg">Check</span>' ?></td></tr>
      </tbody>
    </table>
    <div class="card__sub" style="margin-top:var(--space-xl)">
      <?= $customerCount ?> customers &middot; <?= $employeeCount ?> active employees
    </div>
  </div>
</div>
<?php capistra_layout_footer(); ?>
