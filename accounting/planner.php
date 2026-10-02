<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';

capistra_require_login();
$pdo = capistra_pdo();

// Current cash position as the default starting point.
$cash = (float) $pdo->query('SELECT COALESCE(SUM(l.debit - l.credit),0)
                             FROM journal_lines l
                             JOIN accounts a ON a.id = l.account_id
                             JOIN journal_entries e ON e.id = l.journal_entry_id
                             WHERE a.is_cash_account = 1 AND e.status = \'posted\'')->fetchColumn();

$input = [
    'starting_cash'   => (float) ($_POST['starting_cash'] ?? $cash),
    'monthly_income'  => (float) ($_POST['monthly_income'] ?? 250000),
    'monthly_expense' => (float) ($_POST['monthly_expense'] ?? 200000),
    'planned_invest'  => (float) ($_POST['planned_invest'] ?? 500000),
    'loan_repayment'  => (float) ($_POST['loan_repayment'] ?? 25000),
    'reserve_target'  => (float) ($_POST['reserve_target'] ?? 500000),
    'months'          => max(1, min(36, (int) ($_POST['months'] ?? 12))),
];

$months = $input['months'];
$netMonthly = $input['monthly_income'] - $input['monthly_expense'] - $input['loan_repayment'];
$investMonth = min($months, 1); // planned investment assumed in the first month
$projection = [];
$cashRun = $input['starting_cash'];
for ($m = 1; $m <= $months; $m++) {
    $cashRun += $netMonthly;
    if ($m === $investMonth) { $cashRun -= $input['planned_invest']; }
    $projection[] = ['month' => $m, 'cash' => $cashRun];
}

$finalCash = $projection === [] ? $input['starting_cash'] : $projection[count($projection) - 1]['cash'];
$surplus = $finalCash < 0 ? 0.0 : max(0.0, $finalCash - $input['reserve_target']);
$deficit = max(0.0, $input['reserve_target'] - $finalCash);
$runway = null;
if ($netMonthly < 0) {
    $runway = $input['starting_cash'] > 0 ? (int) floor($input['starting_cash'] / abs($netMonthly)) : 0;
}
$belowReserveMonth = null;
$reserveRun = $input['starting_cash'];
for ($m = 1; $m <= $months; $m++) {
    $reserveRun += $netMonthly;
    if ($m === $investMonth) { $reserveRun -= $input['planned_invest']; }
    if ($reserveRun < $input['reserve_target']) { $belowReserveMonth = $m; break; }
}

capistra_layout_header('Scenario Planner', 'planner.php');
?>
<div class="alert alert--info">
  Planning calculator only. This is a projection based on the figures you enter and is
  <strong>not financial, tax or investment advice</strong>.
</div>

<div class="grid grid--2">
  <div class="card">
    <h2>Assumptions</h2>
    <form method="post" action="planner.php">
      <?= capistra_csrf_field() ?>
      <div class="form-row"><label for="starting_cash">Starting cash</label><input id="starting_cash" name="starting_cash" inputmode="decimal" value="<?= e((string) $input['starting_cash']) ?>"><p class="hint">Defaults to your current posted cash position.</p></div>
      <div class="form-row"><label for="monthly_income">Expected monthly income</label><input id="monthly_income" name="monthly_income" inputmode="decimal" value="<?= e((string) $input['monthly_income']) ?>"></div>
      <div class="form-row"><label for="monthly_expense">Expected monthly operating expense</label><input id="monthly_expense" name="monthly_expense" inputmode="decimal" value="<?= e((string) $input['monthly_expense']) ?>"></div>
      <div class="form-row"><label for="planned_invest">Planned investment (first month)</label><input id="planned_invest" name="planned_invest" inputmode="decimal" value="<?= e((string) $input['planned_invest']) ?>"></div>
      <div class="form-row"><label for="loan_repayment">Monthly loan repayment</label><input id="loan_repayment" name="loan_repayment" inputmode="decimal" value="<?= e((string) $input['loan_repayment']) ?>"></div>
      <div class="form-row"><label for="reserve_target">Cash reserve target</label><input id="reserve_target" name="reserve_target" inputmode="decimal" value="<?= e((string) $input['reserve_target']) ?>"></div>
      <div class="form-row"><label for="months">Months to project</label><input id="months" name="months" type="number" min="1" max="36" value="<?= (int) $months ?>"></div>
      <button class="btn btn--primary" type="submit">Calculate</button>
    </form>
  </div>

  <div>
    <div class="grid grid--kpi">
      <div class="card"><div class="card__label">Net monthly</div><div class="card__value"><?= capistra_amount($netMonthly) ?></div></div>
      <div class="card"><div class="card__label">Projected cash (<?= (int) $months ?> mo)</div><div class="card__value"><?= capistra_amount($finalCash) ?></div></div>
      <div class="card"><div class="card__label">Surplus vs reserve</div><div class="card__value"><?= e(capistra_currency($surplus)) ?></div></div>
      <div class="card"><div class="card__label">Deficit vs reserve</div><div class="card__value"><?= $deficit > 0 ? '<span class="amount amount--neg">' . e(capistra_currency($deficit)) . '</span>' : e(capistra_currency(0)) ?></div></div>
    </div>

    <?php if ($netMonthly < 0): ?>
      <div class="alert alert--warning" style="margin-top:var(--space-xl)">
        Negative monthly cash flow. Estimated runway: <strong><?= $runway === null ? 'n/a' : (int) $runway . ' month(s)' ?></strong>.
      </div>
    <?php endif; ?>
    <?php if ($belowReserveMonth !== null): ?>
      <div class="alert alert--warning" style="margin-top:var(--space-xl)">
        Cash is projected to fall below your reserve target in month <strong><?= (int) $belowReserveMonth ?></strong>.
      </div>
    <?php else: ?>
      <div class="alert alert--success" style="margin-top:var(--space-xl)">Cash stays at or above your reserve target for the full horizon.</div>
    <?php endif; ?>
    <p class="hint">Investment impact: <?= e(capistra_currency($input['planned_invest'])) ?> deployed in month 1. Loan repayment: <?= e(capistra_currency($input['loan_repayment'])) ?>/month.</p>

    <div class="table-wrap" style="margin-top:var(--space-xl)">
      <table class="data">
        <caption class="card__label" style="text-align:left;padding:var(--space-md) var(--space-lg)">Projected cash position</caption>
        <thead><tr><th scope="col">Month</th><th scope="col" class="num">Projected cash</th><th scope="col">vs reserve</th></tr></thead>
        <tbody>
        <?php foreach ($projection as $p): ?>
          <tr>
            <td><?= (int) $p['month'] ?></td>
            <td class="num"><?= capistra_amount((float) $p['cash']) ?></td>
            <td><?= $p['cash'] >= $input['reserve_target'] ? '<span class="badge badge--pos">Above</span>' : '<span class="badge badge--neg">Below</span>' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php capistra_layout_footer(); ?>
