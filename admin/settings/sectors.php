<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
$pdo  = capistra_pdo();
capistra_default_exchange_id(); // ensure the default exchange exists

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            if (capistra_post_string('action') === 'toggle') {
                $id = (int) ($_POST['id'] ?? 0);
                $pdo->prepare('UPDATE market_sectors SET is_active = ? WHERE id = ?')
                    ->execute([!empty($_POST['active']) ? 1 : 0, $id]);
                capistra_audit($pdo, 'update', 'market_sector', $id, 'active state changed');
            } else {
                $id = capistra_sector_save([
                    'id'          => (int) ($_POST['id'] ?? 0),
                    'exchange_id' => capistra_default_exchange_id(),
                    'name'        => capistra_post_string('name'),
                    'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
                    'is_active'   => !empty($_POST['is_active']),
                ]);
                capistra_audit($pdo, 'save', 'market_sector', $id, capistra_post_string('name'));
            }
            capistra_flash_set('success', 'Sector saved.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save sector: ' . $e->getMessage());
        }
    }
    header('Location: sectors.php');
    exit;
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM market_sectors WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}
$counts = [];
foreach ($pdo->query('SELECT sector_id, COUNT(*) c FROM securities GROUP BY sector_id')->fetchAll() as $row) {
    $counts[(int) $row['sector_id']] = (int) $row['c'];
}

capistra_layout_header('Settings - Sectors', 'index.php', '../../');
?>
<h1>Market Sectors</h1>
<p class="hint">Securities link to sectors by ID, so renaming a sector never breaks a holding.</p>
<?php capistra_render_settings_nav('sectors.php'); capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th scope="col">Sector</th><th scope="col" class="num">Securities</th><th scope="col">Active</th><th scope="col">Actions</th></tr></thead>
      <tbody>
      <?php foreach (capistra_sectors(capistra_default_exchange_id()) as $s): ?>
        <tr>
          <th scope="row"><?= e((string) $s['name']) ?></th>
          <td class="num"><?= (int) ($counts[(int) $s['id']] ?? 0) ?></td>
          <td><?= $s['is_active'] ? '<span class="badge badge--pos">Active</span>' : '<span class="badge">Inactive</span>' ?></td>
          <td>
            <a class="btn btn--secondary btn--sm" href="sectors.php?edit=<?= (int) $s['id'] ?>">Edit</a>
            <form method="post" style="display:inline">
              <?= capistra_csrf_field() ?>
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
              <input type="hidden" name="active" value="<?= $s['is_active'] ? '0' : '1' ?>">
              <button class="btn btn--secondary btn--sm" type="submit"><?= $s['is_active'] ? 'Disable' : 'Enable' ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h2><?= $editing ? 'Edit sector' : 'Add sector' ?></h2>
    <form method="post" action="sectors.php">
      <?= capistra_csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
      <div class="form-row"><label for="name">Name</label><input id="name" name="name" required maxlength="150" value="<?= e((string) ($editing['name'] ?? '')) ?>"></div>
      <div class="form-row"><label for="sort_order">Sort order</label><input id="sort_order" name="sort_order" type="number" value="<?= (int) ($editing['sort_order'] ?? 0) ?>"></div>
      <div class="form-row"><label class="switch"><input type="checkbox" name="is_active" value="1" <?= (($editing['is_active'] ?? 1) ? 'checked' : '') ?>><span>Active</span></label></div>
      <button class="btn btn--primary" type="submit">Save sector</button>
      <?php if ($editing): ?><a class="btn btn--secondary" href="sectors.php">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
