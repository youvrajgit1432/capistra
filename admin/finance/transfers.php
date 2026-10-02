<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
$pdo = capistra_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            $id = capistra_post_transfer([
                'from_account_id' => (int) ($_POST['from_account_id'] ?? 0),
                'to_account_id'   => (int) ($_POST['to_account_id'] ?? 0),
                'transfer_date'   => capistra_post_string('transfer_date', date('Y-m-d')),
                'amount'          => (float) capistra_post_string('amount', '0'),
                'reference'       => capistra_post_string('reference'),
                'notes'           => capistra_post_string('notes'),
            ]);
            capistra_audit($pdo, 'create', 'transfer', $id, 'Posted a transfer');
            capistra_flash_set('success', 'Transfer posted (not counted as income or expense).');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Transfer failed: ' . $e->getMessage());
        }
    }
    header('Location: transfers.php');
    exit;
}

$accounts = capistra_financial_accounts(true);
$transfers = capistra_transfers();

capistra_layout_header('Transfers', 'transfers.php', '../../');
?>
<h1>Transfers</h1>
<p class="hint">Move money between wallets. Transfers are balanced ledger entries and are never counted as income or expense.</p>
<?php capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th scope="col">Date</th><th scope="col">From</th><th scope="col">To</th><th scope="col" class="num">Amount</th><th scope="col">Reference</th></tr></thead>
      <tbody>
      <?php foreach ($transfers as $t): ?>
        <tr>
          <th scope="row"><?= e((string) $t['transfer_date']) ?></th>
          <td><?= e((string) $t['from_name']) ?></td>
          <td><?= e((string) $t['to_name']) ?></td>
          <td class="num"><?= e(capistra_money((float) $t['amount'])) ?></td>
          <td><?= e((string) ($t['reference'] ?? '')) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($transfers === []): ?><tr><td class="empty-state" colspan="5">No transfers yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h2>New transfer</h2>
    <form method="post" action="transfers.php">
      <?= capistra_csrf_field() ?>
      <div class="form-row"><label for="from_account_id">From</label>
        <select id="from_account_id" name="from_account_id" required>
          <option value="">Select account…</option>
          <?php foreach ($accounts as $a): ?><option value="<?= (int) $a['id'] ?>"><?= e((string) $a['name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="to_account_id">To</label>
        <select id="to_account_id" name="to_account_id" required>
          <option value="">Select account…</option>
          <?php foreach ($accounts as $a): ?><option value="<?= (int) $a['id'] ?>"><?= e((string) $a['name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="transfer_date">Date</label><input id="transfer_date" name="transfer_date" type="date" value="<?= e(date('Y-m-d')) ?>" required></div>
      <div class="form-row"><label for="amount">Amount</label><input id="amount" name="amount" type="number" step="0.01" required></div>
      <div class="form-row"><label for="reference">Reference</label><input id="reference" name="reference" maxlength="60"></div>
      <div class="form-row"><label for="notes">Notes</label><input id="notes" name="notes" maxlength="255"></div>
      <button class="btn btn--primary" type="submit">Post transfer</button>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
