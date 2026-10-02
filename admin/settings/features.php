<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
$pdo  = capistra_pdo();
$registry = capistra_feature_registry();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            $changed = [];
            foreach (array_keys($registry) as $key) {
                $enabled = !empty($_POST['enable_' . $key]);
                capistra_set_setting($pdo, 'enable_' . $key, $enabled ? '1' : '0');
                $changed[] = $key . '=' . ($enabled ? 'on' : 'off');
            }
            capistra_audit($pdo, 'update', 'feature_flags', null, implode(', ', $changed));
            capistra_flash_set('success', 'Module settings saved. Navigation updates immediately.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save modules: ' . $e->getMessage());
        }
    }
    header('Location: features.php');
    exit;
}

capistra_layout_header('Settings - Modules', 'index.php', '../../');
?>
<h1>Feature Modules</h1>
<p class="hint">Disabled modules are hidden from navigation <strong>and</strong> blocked when accessed directly by URL. Profile mode: <span class="badge"><?= e(capistra_profile_mode()) ?></span></p>
<?php capistra_render_settings_nav('features.php'); capistra_flash_render(); ?>

<form method="post" action="features.php">
  <?= capistra_csrf_field() ?>
  <?php
  $groups = [];
  foreach ($registry as $key => $def) {
      $groups[$def['group']][$key] = $def;
  }
  foreach ($groups as $group => $items): ?>
    <div class="card" style="margin-bottom:var(--space-2xl)">
      <h2><?= e($group) ?></h2>
      <table class="data">
        <thead><tr><th scope="col">Module</th><th scope="col">Description</th><th scope="col">Enabled</th></tr></thead>
        <tbody>
        <?php foreach ($items as $key => $def): ?>
          <tr>
            <th scope="row"><?= e($def['label']) ?></th>
            <td><?= e($def['description']) ?></td>
            <td>
              <label class="switch">
                <input type="checkbox" name="enable_<?= e($key) ?>" value="1" <?= capistra_feature_enabled($key) ? 'checked' : '' ?>>
                <span>Enabled</span>
              </label>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
  <button class="btn btn--primary" type="submit">Save modules</button>
</form>
<?php capistra_layout_footer(); ?>
