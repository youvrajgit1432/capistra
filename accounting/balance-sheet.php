<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';

capistra_require_login();
$pdo = capistra_pdo();

$asOf = (string) ($_GET['as_of'] ?? date('Y-m-d'));
$bs = ledger_balance_sheet($pdo, $asOf);

capistra_layout_header('Balance Sheet', 'balance-sheet.php');
?>
<form class="toolbar" method="get" action="balance-sheet.php">
  <div class="form-row"><label for="as_of">As of</label><input type="date" id="as_of" name="as_of" value="<?= e($asOf) ?>"></div>
  <button class="btn btn--primary" type="submit">Update</button>
  <button class="btn btn--secondary" type="button" onclick="window.print()">Print</button>
</form>

<?php if ($bs['balanced']): ?>
  <div class="alert alert--success">Assets equal liabilities plus equity.</div>
<?php else: ?>
  <div class="alert alert--error">Balance sheet does not balance. Review journal entries.</div>
<?php endif; ?>

<div class="grid grid--2">
  <div class="card">
    <h2>Assets</h2>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th scope="col">Code</th><th scope="col">Account</th><th scope="col" class="num">Amount</th></tr></thead>
        <tbody>
        <?php foreach ($bs['assets'] as $r): ?>
          <tr><td><?= e($r['code']) ?></td><td><?= e($r['name']) ?></td><td class="num"><?= capistra_amount((float) $r['balance'], false) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="2">Total assets</td><td class="num"><?= e(capistra_currency((float) $bs['total_assets'])) ?></td></tr></tfoot>
      </table>
    </div>
  </div>

  <div>
    <div class="card">
      <h2>Liabilities</h2>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th scope="col">Code</th><th scope="col">Account</th><th scope="col" class="num">Amount</th></tr></thead>
          <tbody>
          <?php if ($bs['liabilities'] === []): ?>
            <tr><td colspan="3" class="empty-state">No liabilities.</td></tr>
          <?php else: foreach ($bs['liabilities'] as $r): ?>
            <tr><td><?= e($r['code']) ?></td><td><?= e($r['name']) ?></td><td class="num"><?= e(capistra_currency((float) $r['amount'])) ?></td></tr>
          <?php endforeach; endif; ?>
          </tbody>
          <tfoot><tr><td colspan="2">Total liabilities</td><td class="num"><?= e(capistra_currency((float) $bs['total_liabilities'])) ?></td></tr></tfoot>
        </table>
      </div>
    </div>

    <div class="card" style="margin-top:var(--space-xl)">
      <h2>Equity</h2>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th scope="col">Code</th><th scope="col">Account</th><th scope="col" class="num">Amount</th></tr></thead>
          <tbody>
          <?php foreach ($bs['equity'] as $r): ?>
            <tr><td><?= e($r['code']) ?></td><td><?= e($r['name']) ?></td><td class="num"><?= capistra_amount((float) $r['amount'], false) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><td colspan="2">Total liabilities + equity</td><td class="num"><?= e(capistra_currency((float) $bs['total_liabilities_and_equity'])) ?></td></tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
<?php capistra_layout_footer(); ?>
