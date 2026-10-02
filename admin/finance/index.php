<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
capistra_guard_module('net_worth');
$pdo = capistra_pdo();

$asOf = (string) ($_GET['as_of'] ?? date('Y-m-d'));
$report = capistra_net_worth_report($asOf);
$insights = capistra_insights();
$due = capistra_recurring_due(date('Y-m-d', strtotime('+7 days')));

capistra_layout_header('Net Worth & Overview', 'index.php', '../../');
?>
<h1>Net Worth &amp; Overview</h1>
<p class="hint">Assets minus liabilities, derived from financial accounts and investments. Valuation as of <?= e($asOf) ?>.</p>

<div class="grid grid--kpi">
  <div class="card"><div class="card__label">Total assets</div><div class="card__value"><?= e(capistra_money($report['total_assets'])) ?></div></div>
  <div class="card"><div class="card__label">Total liabilities</div><div class="card__value"><?= e(capistra_money($report['total_liabilities'])) ?></div></div>
  <div class="card"><div class="card__label">Net worth</div><div class="card__value"><?= capistra_amount($report['net']) ?></div></div>
</div>

<div class="grid grid--2" style="margin-top:var(--space-3xl)">
  <div class="card">
    <h2>Assets</h2>
    <table class="data">
      <tbody>
      <?php foreach ($report['assets'] as $name => $value): ?>
        <tr><th scope="row"><?= e((string) $name) ?></th><td class="num"><?= e(capistra_money((float) $value)) ?></td></tr>
      <?php endforeach; ?>
      <?php if ($report['assets'] === []): ?><tr><td class="empty-state">No assets recorded.</td></tr><?php endif; ?>
      </tbody>
      <tfoot><tr><td>Total</td><td class="num"><?= e(capistra_money($report['total_assets'])) ?></td></tr></tfoot>
    </table>
  </div>
  <div class="card">
    <h2>Liabilities</h2>
    <table class="data">
      <tbody>
      <?php foreach ($report['liabilities'] as $name => $value): ?>
        <tr><th scope="row"><?= e((string) $name) ?></th><td class="num"><?= e(capistra_money((float) $value)) ?></td></tr>
      <?php endforeach; ?>
      <?php if ($report['liabilities'] === []): ?><tr><td class="empty-state">No liabilities recorded.</td></tr><?php endif; ?>
      </tbody>
      <tfoot><tr><td>Total</td><td class="num"><?= e(capistra_money($report['total_liabilities'])) ?></td></tr></tfoot>
    </table>
  </div>
</div>

<div class="grid grid--2" style="margin-top:var(--space-3xl)">
  <div class="card">
    <h2>Financial health insights</h2>
    <p class="hint">Deterministic rules — not financial advice.</p>
    <?php if ($insights === []): ?>
      <p class="empty-state">Nothing to flag right now.</p>
    <?php else: foreach ($insights as $i): ?>
      <div class="alert <?= $i['level'] === 'error' ? 'alert--error' : ($i['level'] === 'warning' ? 'alert--info' : 'alert--success') ?>">
        <strong><?= e($i['title']) ?>:</strong> <?= e($i['detail']) ?>
      </div>
    <?php endforeach; endif; ?>
  </div>
  <div class="card">
    <h2>Upcoming recurring (7 days)</h2>
    <table class="data">
      <thead><tr><th scope="col">Type</th><th scope="col">Next date</th><th scope="col" class="num">Amount</th></tr></thead>
      <tbody>
      <?php foreach ($due as $rule): ?>
        <tr><th scope="row"><?= e(ucfirst((string) $rule['transaction_type'])) ?></th><td><?= e((string) $rule['next_date']) ?></td><td class="num"><?= e(capistra_money((float) $rule['amount'])) ?></td></tr>
      <?php endforeach; ?>
      <?php if ($due === []): ?><tr><td class="empty-state" colspan="3">Nothing due.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php capistra_layout_footer(); ?>
