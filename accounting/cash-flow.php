<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';

capistra_require_login();
$pdo = capistra_pdo();

$from = (string) ($_GET['from'] ?? date('Y-01-01'));
$to   = (string) ($_GET['to'] ?? date('Y-m-d'));
$cf = ledger_cash_flow($pdo, $from, $to);

capistra_layout_header('Cash Flow', 'cash-flow.php');
?>
<form class="toolbar" method="get" action="cash-flow.php">
  <div class="form-row"><label for="from">From</label><input type="date" id="from" name="from" value="<?= e($from) ?>"></div>
  <div class="form-row"><label for="to">To</label><input type="date" id="to" name="to" value="<?= e($to) ?>"></div>
  <button class="btn btn--primary" type="submit">Update</button>
  <button class="btn btn--secondary" type="button" onclick="window.print()">Print</button>
</form>

<div class="grid grid--kpi">
  <div class="card"><div class="card__label">Operating</div><div class="card__value"><?= capistra_amount((float) $cf['operating']) ?></div></div>
  <div class="card"><div class="card__label">Investing</div><div class="card__value"><?= capistra_amount((float) $cf['investing']) ?></div></div>
  <div class="card"><div class="card__label">Financing</div><div class="card__value"><?= capistra_amount((float) $cf['financing']) ?></div></div>
  <div class="card"><div class="card__label">Net change in cash</div><div class="card__value"><?= capistra_amount((float) $cf['net']) ?></div></div>
</div>

<div class="table-wrap" style="margin-top:var(--space-2xl)">
  <table class="data">
    <caption class="card__label" style="text-align:left;padding:var(--space-md) var(--space-lg)">Cash movement by journal source type</caption>
    <thead><tr><th scope="col">Source</th><th scope="col">Bucket</th><th scope="col" class="num">Net cash</th></tr></thead>
    <tbody>
    <?php if ($cf['rows'] === []): ?>
      <tr><td colspan="3" class="empty-state">No cash movements in this period.</td></tr>
    <?php else: foreach ($cf['rows'] as $r): ?>
      <tr>
        <td><?= e((string) $r['source_type']) ?></td>
        <td><span class="badge"><?= e((string) $r['bucket']) ?></span></td>
        <td class="num"><?= capistra_amount((float) $r['net']) ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<p class="hint">Approximate management summary derived from journal source types. Not a statutory cash-flow statement.</p>
<?php capistra_layout_footer(); ?>
