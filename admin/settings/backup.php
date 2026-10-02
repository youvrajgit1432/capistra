<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();

capistra_layout_header('Settings - Backup', 'index.php', '../../');
?>
<h1>Backup</h1>
<p class="hint">Backups are managed by the existing backup tooling. Backups are runtime artifacts and are intentionally excluded from version control.</p>
<?php capistra_render_settings_nav('backup.php'); capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="card">
    <h2>Database backup</h2>
    <p class="hint">Create and download a database backup using the maintained backup screen.</p>
    <a class="btn btn--primary" href="<?= e(capistra_url('backup_ui.php')) ?>">Open Backup Manager</a>
  </div>
  <div class="card">
    <h2>Restore guidance</h2>
    <ul class="hint">
      <li>Backups are written outside the web root where possible.</li>
      <li>Restoring should be done on a maintenance window with writes stopped.</li>
      <li>Never commit backup files or uploaded documents to Git.</li>
    </ul>
  </div>
</div>
<?php capistra_layout_footer(); ?>
