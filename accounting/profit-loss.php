<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';

capistra_require_login();
$pdo = capistra_pdo();

$from = (string) ($_GET['from'] ?? date('Y-01-01'));
$to   = (string) ($_GET['to'] ?? date('Y-m-d'));
$pl = ledger_profit_loss($pdo, $from, $to);

capistra_layout_header('Profit & Loss', 'profit-loss.php');
?>
<form class="toolbar" method="get" action="profit-loss.php">
  <div class="form-row"><label for="from">From</label><input type="date" id="from" name="from" value="<?= e($from) ?>"></div>
  <div class="form-row"><label for="to">To</label><input type="date" id="to" name="to" value="<?= e($to) ?>"></div>
  <button class="btn btn--primary" type="submit">Update</button>
  <button class="btn btn--secondary" type="button" onclick="window.print()">Print</button>
</form>

<div class="grid grid--2">
  <div class="card">
    <h2>Income</h2>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th scope="col">Code</th><th scope="col">Account</th><th scope="col" class="num">Amount</th></tr></thead>
        <tbody>
        <?php if ($pl['income'] === []): ?>
          <tr><td colspan="3" class="empty-state">No income in this period.</td></tr>
        <?php else: foreach ($pl['income'] as $r): ?>
          <tr><td><?= e($r['code']) ?></td><td><?= e($r['name']) ?></td><td class="num"><?= e(capistra_currency((float) $r['amount'])) ?></td></tr>
        <?php endforeach; endif; ?>
        </tbody>
        <tfoot><tr><td colspan="2">Total income</td><td class="num"><?= e(capistra_currency((float) $pl['total_income'])) ?></td></tr></tfoot>
      </table>
    </div>
  </div>

  <div class="card">
    <h2>Expenses</h2>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th scope="col">Code</th><th scope="col">Account</th><th scope="col" class="num">Amount</th></tr></thead>
        <tbody>
        <?php if ($pl['expense'] === []): ?>
          <tr><td colspan="3" class="empty-state">No expenses in this period.</td></tr>
        <?php else: foreach ($pl['expense'] as $r): ?>
          <tr><td><?= e($r['code']) ?></td><td><?= e($r['name']) ?></td><td class="num"><?= e(capistra_currency((float) $r['amount'])) ?></td></tr>
        <?php endforeach; endif; ?>
        </tbody>
        <tfoot><tr><td colspan="2">Total expense</td><td class="num"><?= e(capistra_currency((float) $pl['total_expense'])) ?></td></tr></tfoot>
      </table>
    </div>
  </div>
</div>

<div class="card" style="margin-top:var(--space-2xl)">
  <div class="card__label">Net result for <?= e($from) ?> to <?= e($to) ?></div>
  <div class="card__value"><?= capistra_amount((float) $pl['net']) ?></div>
</div>
<?php capistra_layout_footer(); ?>
