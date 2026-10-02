<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
$pdo  = capistra_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            $id = capistra_financial_account_save([
                'id'               => (int) ($_POST['id'] ?? 0),
                'name'             => capistra_post_string('name'),
                'type'             => capistra_post_string('type', 'cash'),
                'institution'      => capistra_post_string('institution'),
                'currency'         => capistra_post_string('currency', capistra_base_currency()),
                'opening_balance'  => capistra_post_string('opening_balance', '0'),
                'masked_reference' => capistra_post_string('masked_reference'),
                'ledger_account_id'=> (int) ($_POST['ledger_account_id'] ?? 0),
                'is_active'        => !empty($_POST['is_active']),
                'notes'            => capistra_post_string('notes'),
            ]);
            capistra_audit($pdo, 'save', 'financial_account', $id, capistra_post_string('name'));
            capistra_flash_set('success', 'Account saved.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save account: ' . $e->getMessage());
        }
    }
    header('Location: accounts.php');
    exit;
}

$editing = isset($_GET['edit']) ? capistra_financial_account_get((int) $_GET['edit']) : null;
$accounts = $pdo->query("SELECT id, code, name, type FROM accounts WHERE type IN ('asset','liability') ORDER BY code")->fetchAll();

capistra_layout_header('Settings - Accounts', 'index.php', '../../');
?>
<h1>Accounts &amp; Wallets</h1>
<p class="hint">Real-world money containers (cash, bank, e-wallet, broker, credit card, loan). Each links to a ledger account so transactions post correctly. Never store online-banking passwords here.</p>
<?php capistra_render_settings_nav('accounts.php'); capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th scope="col">Account</th><th scope="col">Type</th><th scope="col">Ledger</th><th scope="col" class="num">Balance</th><th scope="col">Actions</th></tr></thead>
      <tbody>
      <?php foreach (capistra_financial_accounts() as $acc): ?>
        <?php $bal = capistra_financial_account_balance((int) $acc['id']); ?>
        <tr>
          <th scope="row"><?= e((string) $acc['name']) ?><?php if (!$acc['is_active']): ?> <span class="badge">Inactive</span><?php endif; ?>
            <?php if ($acc['institution']): ?><div class="hint"><?= e((string) $acc['institution']) ?><?= $acc['masked_reference'] ? ' · ' . e((string) $acc['masked_reference']) : '' ?></div><?php endif; ?>
          </th>
          <td><?= e(capistra_account_types()[$acc['type']] ?? (string) $acc['type']) ?></td>
          <td><?= $acc['ledger_code'] ? e($acc['ledger_code'] . ' · ' . $acc['ledger_name']) : '<span class="badge badge--neg">unmapped</span>' ?></td>
          <td class="num"><?= $bal === null ? '—' : e(capistra_money($bal)) ?></td>
          <td><a class="btn btn--secondary btn--sm" href="accounts.php?edit=<?= (int) $acc['id'] ?>">Edit</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2><?= $editing ? 'Edit account' : 'Add account' ?></h2>
    <form method="post" action="accounts.php">
      <?= capistra_csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
      <div class="form-row"><label for="name">Name</label><input id="name" name="name" required maxlength="150" value="<?= e((string) ($editing['name'] ?? '')) ?>"></div>
      <div class="form-row"><label for="type">Type</label>
        <select id="type" name="type">
          <?php foreach (capistra_account_types() as $v => $l): ?>
            <option value="<?= e($v) ?>" <?= ($editing['type'] ?? 'cash') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="institution">Institution</label><input id="institution" name="institution" maxlength="150" value="<?= e((string) ($editing['institution'] ?? '')) ?>"></div>
      <div class="form-row"><label for="currency">Currency</label><input id="currency" name="currency" maxlength="3" value="<?= e((string) ($editing['currency'] ?? capistra_base_currency())) ?>"></div>
      <div class="form-row"><label for="opening_balance">Opening balance</label><input id="opening_balance" name="opening_balance" type="number" step="0.01" value="<?= e((string) ($editing['opening_balance'] ?? '0')) ?>"></div>
      <div class="form-row"><label for="masked_reference">Masked reference</label><input id="masked_reference" name="masked_reference" maxlength="60" placeholder="****1234" value="<?= e((string) ($editing['masked_reference'] ?? '')) ?>"></div>
      <div class="form-row"><label for="ledger_account_id">Ledger account</label>
        <select id="ledger_account_id" name="ledger_account_id" required>
          <option value="">Select account…</option>
          <?php foreach ($accounts as $a): ?>
            <option value="<?= (int) $a['id'] ?>" <?= (int) ($editing['ledger_account_id'] ?? 0) === (int) $a['id'] ? 'selected' : '' ?>><?= e($a['code'] . ' · ' . $a['name'] . ' (' . $a['type'] . ')') ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label class="switch"><input type="checkbox" name="is_active" value="1" <?= (($editing['is_active'] ?? 1) ? 'checked' : '') ?>><span>Active</span></label></div>
      <div class="form-row"><label for="notes">Notes</label><textarea id="notes" name="notes" rows="2"><?= e((string) ($editing['notes'] ?? '')) ?></textarea></div>
      <button class="btn btn--primary" type="submit">Save account</button>
      <?php if ($editing): ?><a class="btn btn--secondary" href="accounts.php">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
