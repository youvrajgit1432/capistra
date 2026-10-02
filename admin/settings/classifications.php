<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
$pdo  = capistra_pdo();
$groups = capistra_classification_groups();
$group = (string) ($_GET['group'] ?? array_key_first($groups));
if (!isset($groups[$group])) { $group = array_key_first($groups); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            if (capistra_post_string('action') === 'toggle') {
                $id = (int) ($_POST['id'] ?? 0);
                $pdo->prepare('UPDATE classification_options SET is_active = ? WHERE id = ?')
                    ->execute([!empty($_POST['active']) ? 1 : 0, $id]);
            } else {
                $id = capistra_classification_save([
                    'id'        => (int) ($_POST['id'] ?? 0),
                    'group_key' => capistra_post_string('group_key', $group),
                    'label'     => capistra_post_string('label'),
                    'sort_order'=> (int) ($_POST['sort_order'] ?? 0),
                    'is_active' => !empty($_POST['is_active']),
                ]);
                capistra_audit($pdo, 'save', 'classification_option', $id, capistra_post_string('label'));
            }
            capistra_flash_set('success', 'Option saved.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save option: ' . $e->getMessage());
        }
    }
    header('Location: classifications.php?group=' . urlencode($group));
    exit;
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM classification_options WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

capistra_layout_header('Settings - Classifications', 'index.php', '../../');
?>
<h1>Classification Options</h1>
<p class="hint">Values an operator is reasonably expected to change are data-driven here. Invariant system states (account types, transaction types) stay as code enums.</p>
<?php capistra_render_settings_nav('classifications.php'); capistra_flash_render(); ?>

<nav class="tabs" aria-label="Classification groups">
  <?php foreach ($groups as $key => $label): ?>
    <a class="tab" href="classifications.php?group=<?= e($key) ?>" <?= $group === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>

<div class="grid grid--2">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th scope="col">Label</th><th scope="col" class="num">Order</th><th scope="col">Active</th><th scope="col">Actions</th></tr></thead>
      <tbody>
      <?php foreach (capistra_classification_options($group, false) as $o): ?>
        <tr>
          <th scope="row"><?= e((string) $o['label']) ?></th>
          <td class="num"><?= (int) $o['sort_order'] ?></td>
          <td><?= $o['is_active'] ? 'Yes' : 'No' ?></td>
          <td>
            <a class="btn btn--secondary btn--sm" href="classifications.php?group=<?= e($group) ?>&edit=<?= (int) $o['id'] ?>">Edit</a>
            <form method="post" style="display:inline">
              <?= capistra_csrf_field() ?>
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
              <input type="hidden" name="active" value="<?= $o['is_active'] ? '0' : '1' ?>">
              <button class="btn btn--secondary btn--sm" type="submit"><?= $o['is_active'] ? 'Disable' : 'Enable' ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h2><?= $editing ? 'Edit option' : 'Add option' ?> — <?= e($groups[$group]) ?></h2>
    <form method="post" action="classifications.php?group=<?= e($group) ?>">
      <?= capistra_csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
      <input type="hidden" name="group_key" value="<?= e($group) ?>">
      <div class="form-row"><label for="label">Label</label><input id="label" name="label" required maxlength="150" value="<?= e((string) ($editing['label'] ?? '')) ?>"></div>
      <div class="form-row"><label for="sort_order">Sort order</label><input id="sort_order" name="sort_order" type="number" value="<?= (int) ($editing['sort_order'] ?? 0) ?>"></div>
      <div class="form-row"><label class="switch"><input type="checkbox" name="is_active" value="1" <?= (($editing['is_active'] ?? 1) ? 'checked' : '') ?>><span>Active</span></label></div>
      <button class="btn btn--primary" type="submit">Save option</button>
      <?php if ($editing): ?><a class="btn btn--secondary" href="classifications.php?group=<?= e($group) ?>">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
