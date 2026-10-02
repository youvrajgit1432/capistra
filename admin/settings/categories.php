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
            $action = capistra_post_string('action', 'save');
            if ($action === 'toggle') {
                $id = (int) ($_POST['id'] ?? 0);
                capistra_category_set_active($id, !empty($_POST['active']));
                capistra_audit($pdo, 'update', 'transaction_category', $id, 'Active state changed');
                capistra_flash_set('success', 'Category updated.');
            } else {
                $id = (int) ($_POST['id'] ?? 0);
                $saved = capistra_category_save([
                    'id'               => $id,
                    'type'             => capistra_post_string('type', 'expense'),
                    'name'             => capistra_post_string('name'),
                    'description'      => capistra_post_string('description'),
                    'ledger_account_id'=> (int) ($_POST['ledger_account_id'] ?? 0),
                    'color'            => capistra_post_string('color'),
                    'sort_order'       => (int) ($_POST['sort_order'] ?? 0),
                    'is_active'        => !empty($_POST['is_active']),
                ]);
                capistra_audit($pdo, $id > 0 ? 'update' : 'create', 'transaction_category', $saved, capistra_post_string('name'));
                capistra_flash_set('success', 'Category saved.');
            }
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save category: ' . $e->getMessage());
        }
    }
    header('Location: categories.php');
    exit;
}

$editing = null;
if (isset($_GET['edit'])) {
    $editing = capistra_category_get((int) $_GET['edit']);
}
$accounts = $pdo->query("SELECT id, code, name, type FROM accounts WHERE type IN ('income','expense') ORDER BY type, code")->fetchAll();

capistra_layout_header('Settings - Categories', 'index.php', '../../');
?>
<h1>Transaction Categories</h1>
<p class="hint">Every category is mapped to a ledger account. Posting uses this mapping — no more hard-coded name→code tables. Deactivating a category keeps existing transactions intact.</p>
<?php capistra_render_settings_nav('categories.php'); capistra_flash_render(); ?>

<div class="grid grid--2">
  <div>
    <?php foreach (['income' => 'Income Categories', 'expense' => 'Expense Categories'] as $type => $label): ?>
      <h2><?= e($label) ?></h2>
      <div class="table-wrap" style="margin-bottom:var(--space-2xl)">
        <table class="data">
          <thead><tr><th scope="col">Name</th><th scope="col">Ledger account</th><th scope="col" class="num">Order</th><th scope="col">Active</th><th scope="col">Actions</th></tr></thead>
          <tbody>
          <?php foreach (capistra_categories($type) as $cat): ?>
            <tr>
              <th scope="row"><?= e((string) $cat['name']) ?><?php if (!$cat['ledger_account_id']): ?> <span class="badge badge--neg">unmapped</span><?php endif; ?></th>
              <td><?= $cat['ledger_code'] ? e($cat['ledger_code'] . ' · ' . $cat['ledger_name']) : '—' ?></td>
              <td class="num"><?= (int) $cat['sort_order'] ?></td>
              <td><?= $cat['is_active'] ? '<span class="badge badge--pos">Active</span>' : '<span class="badge">Inactive</span>' ?></td>
              <td>
                <a class="btn btn--secondary btn--sm" href="categories.php?edit=<?= (int) $cat['id'] ?>">Edit</a>
                <form method="post" style="display:inline">
                  <?= capistra_csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                  <input type="hidden" name="active" value="<?= $cat['is_active'] ? '0' : '1' ?>">
                  <button class="btn btn--secondary btn--sm" type="submit"><?= $cat['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  </div>

  <div>
    <div class="card">
      <h2><?= $editing ? 'Edit category' : 'Add category' ?></h2>
      <form method="post" action="categories.php">
        <?= capistra_csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
        <div class="form-row"><label for="type">Type</label>
          <select id="type" name="type" required>
            <?php foreach (['income' => 'Income', 'expense' => 'Expense'] as $v => $l): ?>
              <option value="<?= e($v) ?>" <?= ($editing['type'] ?? 'expense') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-row"><label for="name">Name</label>
          <input id="name" name="name" required maxlength="150" value="<?= e((string) ($editing['name'] ?? '')) ?>"></div>
        <div class="form-row">
          <label for="ledger_account_id">Ledger account</label>
          <select id="ledger_account_id" name="ledger_account_id" required>
            <option value="">Select account…</option>
            <?php foreach ($accounts as $a): ?>
              <option value="<?= (int) $a['id'] ?>" <?= (int) ($editing['ledger_account_id'] ?? 0) === (int) $a['id'] ? 'selected' : '' ?>><?= e($a['code'] . ' · ' . $a['name'] . ' (' . $a['type'] . ')') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row"><label for="description">Description</label>
          <input id="description" name="description" maxlength="255" value="<?= e((string) ($editing['description'] ?? '')) ?>"></div>
        <div class="form-row"><label for="sort_order">Sort order</label>
          <input id="sort_order" name="sort_order" type="number" value="<?= (int) ($editing['sort_order'] ?? 0) ?>"></div>
        <div class="form-row">
          <label class="switch"><input type="checkbox" name="is_active" value="1" <?= (($editing['is_active'] ?? 1) ? 'checked' : '') ?>><span>Active</span></label>
        </div>
        <button class="btn btn--primary" type="submit">Save category</button>
        <?php if ($editing): ?><a class="btn btn--secondary" href="categories.php">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
</div>
<?php capistra_layout_footer(); ?>
