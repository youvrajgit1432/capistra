<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
capistra_guard_module('recurring');
$pdo = capistra_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            if (capistra_post_string('action') === 'toggle') {
                $id = (int) ($_POST['id'] ?? 0);
                $pdo->prepare('UPDATE recurring_transactions SET is_active = ? WHERE id = ?')->execute([!empty($_POST['active']) ? 1 : 0, $id]);
                capistra_flash_set('success', 'Rule updated.');
            } else {
                $id = capistra_recurring_save([
                    'id'                  => (int) ($_POST['id'] ?? 0),
                    'transaction_type'    => capistra_post_string('transaction_type', 'expense'),
                    'category_id'         => (int) ($_POST['category_id'] ?? 0),
                    'financial_account_id'=> (int) ($_POST['financial_account_id'] ?? 0),
                    'to_account_id'       => (int) ($_POST['to_account_id'] ?? 0),
                    'amount'              => capistra_post_string('amount', '0'),
                    'frequency'           => capistra_post_string('frequency', 'monthly'),
                    'next_date'           => capistra_post_string('next_date', date('Y-m-d')),
                    'end_date'            => capistra_post_string('end_date'),
                    'mode'                => capistra_post_string('mode', 'reminder'),
                    'is_active'           => !empty($_POST['is_active']),
                    'notes'               => capistra_post_string('notes'),
                ]);
                capistra_audit($pdo, 'save', 'recurring_transaction', $id, capistra_post_string('transaction_type'));
                capistra_flash_set('success', 'Recurring rule saved.');
            }
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save rule: ' . $e->getMessage());
        }
    }
    header('Location: recurring.php');
    exit;
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM recurring_transactions WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}
$rules = capistra_recurring_rules();
$accounts = capistra_financial_accounts(true);
$categories = capistra_categories(null, true);

capistra_layout_header('Recurring Transactions', 'recurring.php', '../../');
?>
<h1>Recurring Transactions</h1>
<p class="hint">Salary, rent, bills and investment contributions. Rules can auto-create a transaction or simply remind you.</p>
<?php capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th scope="col">Type</th><th scope="col">Category / route</th><th scope="col" class="num">Amount</th><th scope="col">Frequency</th><th scope="col">Next</th><th scope="col">Mode</th><th scope="col">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rules as $r): ?>
        <tr>
          <th scope="row"><?= e(ucfirst((string) $r['transaction_type'])) ?><?php if (!$r['is_active']): ?> <span class="badge">Off</span><?php endif; ?></th>
          <td><?= $r['transaction_type'] === 'transfer'
                ? e((string) $r['account_name'] . ' → ' . (string) $r['to_account_name'])
                : e((string) ($r['category_name'] ?? '—')) ?></td>
          <td class="num"><?= e(capistra_money((float) $r['amount'])) ?></td>
          <td><?= e((string) $r['frequency']) ?></td>
          <td><?= e((string) $r['next_date']) ?></td>
          <td><?= e((string) $r['mode']) ?></td>
          <td>
            <a class="btn btn--secondary btn--sm" href="recurring.php?edit=<?= (int) $r['id'] ?>">Edit</a>
            <form method="post" style="display:inline">
              <?= capistra_csrf_field() ?><input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <input type="hidden" name="active" value="<?= $r['is_active'] ? '0' : '1' ?>">
              <button class="btn btn--secondary btn--sm" type="submit"><?= $r['is_active'] ? 'Disable' : 'Enable' ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h2><?= $editing ? 'Edit rule' : 'Add rule' ?></h2>
    <form method="post" action="recurring.php">
      <?= capistra_csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
      <div class="form-row"><label for="transaction_type">Type</label>
        <select id="transaction_type" name="transaction_type">
          <?php foreach (['expense' => 'Expense', 'income' => 'Income', 'transfer' => 'Transfer'] as $v => $l): ?>
            <option value="<?= e($v) ?>" <?= ($editing['transaction_type'] ?? 'expense') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="category_id">Category</label>
        <select id="category_id" name="category_id"><option value="0">—</option>
          <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) ($editing['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['type'] . ' · ' . $c['name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="financial_account_id">Account</label>
        <select id="financial_account_id" name="financial_account_id"><option value="0">—</option>
          <?php foreach ($accounts as $a): ?><option value="<?= (int) $a['id'] ?>" <?= (int) ($editing['financial_account_id'] ?? 0) === (int) $a['id'] ? 'selected' : '' ?>><?= e((string) $a['name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="to_account_id">Destination (transfers)</label>
        <select id="to_account_id" name="to_account_id"><option value="0">—</option>
          <?php foreach ($accounts as $a): ?><option value="<?= (int) $a['id'] ?>" <?= (int) ($editing['to_account_id'] ?? 0) === (int) $a['id'] ? 'selected' : '' ?>><?= e((string) $a['name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="amount">Amount</label><input id="amount" name="amount" type="number" step="0.01" required value="<?= e((string) ($editing['amount'] ?? '')) ?>"></div>
      <div class="form-row"><label for="frequency">Frequency</label>
        <select id="frequency" name="frequency">
          <?php foreach (capistra_recurring_frequencies() as $v => $l): ?><option value="<?= e($v) ?>" <?= ($editing['frequency'] ?? 'monthly') === $v ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="next_date">Next date</label><input id="next_date" name="next_date" type="date" value="<?= e((string) ($editing['next_date'] ?? date('Y-m-d'))) ?>"></div>
      <div class="form-row"><label for="end_date">End date</label><input id="end_date" name="end_date" type="date" value="<?= e((string) ($editing['end_date'] ?? '')) ?>"></div>
      <div class="form-row"><label for="mode">Mode</label>
        <select id="mode" name="mode">
          <option value="reminder" <?= ($editing['mode'] ?? 'reminder') === 'reminder' ? 'selected' : '' ?>>Reminder only</option>
          <option value="auto" <?= ($editing['mode'] ?? '') === 'auto' ? 'selected' : '' ?>>Auto-create</option>
        </select></div>
      <div class="form-row"><label class="switch"><input type="checkbox" name="is_active" value="1" <?= (($editing['is_active'] ?? 1) ? 'checked' : '') ?>><span>Active</span></label></div>
      <button class="btn btn--primary" type="submit">Save rule</button>
      <?php if ($editing): ?><a class="btn btn--secondary" href="recurring.php">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
