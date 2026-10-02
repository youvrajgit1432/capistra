<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';

capistra_require_login();
$pdo = capistra_pdo();

$asOf = (string) ($_GET['as_of'] ?? date('Y-m-d'));
$tb = ledger_trial_balance($pdo, $asOf);

capistra_layout_header('Trial Balance', 'trial-balance.php');
?>
<form class="toolbar" method="get" action="trial-balance.php">
  <div class="form-row"><label for="as_of">As of</label><input type="date" id="as_of" name="as_of" value="<?= e($asOf) ?>"></div>
  <button class="btn btn--primary" type="submit">Update</button>
  <button class="btn btn--secondary" type="button" onclick="window.print()">Print</button>
</form>

<?php if ($tb['balanced']): ?>
  <div class="alert alert--success">Trial balance is balanced: total debit equals total credit.</div>
<?php else: ?>
  <div class="alert alert--error">Trial balance is OUT OF BALANCE. Review journal entries.</div>
<?php endif; ?>

<div class="table-wrap">
  <table class="data">
    <caption class="card__label" style="text-align:left;padding:var(--space-md) var(--space-lg)">Trial balance as of <?= e($asOf) ?></caption>
    <thead>
      <tr><th scope="col">Code</th><th scope="col">Account</th><th scope="col" class="num">Debit</th><th scope="col" class="num">Credit</th></tr>
    </thead>
    <tbody>
    <?php foreach ($tb['rows'] as $row): ?>
      <?php if ($row['debit'] == 0.0 && $row['credit'] == 0.0) continue; ?>
      <tr>
        <td><?= e($row['code']) ?></td>
        <td><?= e($row['name']) ?></td>
        <td class="num"><?= $row['debit'] ? e(capistra_currency((float) $row['debit'])) : '' ?></td>
        <td class="num"><?= $row['credit'] ? e(capistra_currency((float) $row['credit'])) : '' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">Total</td>
        <td class="num"><?= e(capistra_currency((float) $tb['total_debit'])) ?></td>
        <td class="num"><?= e(capistra_currency((float) $tb['total_credit'])) ?></td>
      </tr>
    </tfoot>
  </table>
</div>
<?php capistra_layout_footer(); ?>
