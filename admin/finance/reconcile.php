<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
capistra_guard_module('reconciliation');
$pdo = capistra_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            capistra_reconcile_save([
                'financial_account_id' => (int) ($_POST['financial_account_id'] ?? 0),
                'statement_date'       => capistra_post_string('statement_date', date('Y-m-d')),
                'statement_balance'    => capistra_post_string('statement_balance', '0'),
                'notes'                => capistra_post_string('notes'),
            ]);
            capistra_audit($pdo, 'create', 'reconciliation', null, 'Recorded a reconciliation');
            capistra_flash_set('success', 'Reconciliation recorded.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not reconcile: ' . $e->getMessage());
        }
    }
    header('Location: reconcile.php');
    exit;
}

$accounts = capistra_financial_accounts(true);
$rows = capistra_reconciliations();
$balances = [];
foreach ($accounts as $a) {
    $balances[(int) $a['id']] = capistra_financial_account_balance((int) $a['id']);
}

capistra_layout_header('Reconciliation', 'reconcile.php', '../../');
?>
<h1>Reconciliation</h1>
<p class="hint">Compare a statement balance against the system balance for an account and record the difference.</p>
<?php capistra_flash_render(); ?>

<div class="grid grid--2">
  <div>
    <div class="card">
      <h2>Account balances</h2>
      <table class="data">
        <tbody>
        <?php foreach ($accounts as $a): ?>
          <tr><th scope="row"><?= e((string) $a['name']) ?></th><td class="num"><?= $balances[(int) $a['id']] === null ? '—' : e(capistra_money((float) $balances[(int) $a['id']])) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="table-wrap" style="margin-top:var(--space-2xl)">
      <table class="data">
        <thead><tr><th scope="col">Account</th><th scope="col">Date</th><th scope="col" class="num">Statement</th><th scope="col" class="num">System</th><th scope="col" class="num">Diff</th><th scope="col">Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <th scope="row"><?= e((string) $r['account_name']) ?></th>
            <td><?= e((string) $r['statement_date']) ?></td>
            <td class="num"><?= e(capistra_money((float) $r['statement_balance'])) ?></td>
            <td class="num"><?= e(capistra_money((float) $r['system_balance'])) ?></td>
            <td class="num"><?= capistra_amount((float) $r['difference']) ?></td>
            <td><?= $r['status'] === 'balanced' ? '<span class="badge badge--pos">Balanced</span>' : '<span class="badge badge--neg">Discrepancy</span>' ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if ($rows === []): ?><tr><td class="empty-state" colspan="6">No reconciliations yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card">
    <h2>Record reconciliation</h2>
    <form method="post" action="reconcile.php">
      <?= capistra_csrf_field() ?>
      <div class="form-row"><label for="financial_account_id">Account</label>
        <select id="financial_account_id" name="financial_account_id" required>
          <option value="">Select account…</option>
          <?php foreach ($accounts as $a): ?><option value="<?= (int) $a['id'] ?>"><?= e((string) $a['name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="statement_date">Statement date</label><input id="statement_date" name="statement_date" type="date" value="<?= e(date('Y-m-d')) ?>" required></div>
      <div class="form-row"><label for="statement_balance">Statement ending balance</label><input id="statement_balance" name="statement_balance" type="number" step="0.01" required></div>
      <div class="form-row"><label for="notes">Notes</label><input id="notes" name="notes" maxlength="255"></div>
      <button class="btn btn--primary" type="submit">Record</button>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
