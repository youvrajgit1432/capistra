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
            if ($action === 'close' || $action === 'reopen') {
                $id = (int) ($_POST['id'] ?? 0);
                $status = $action === 'close' ? 'closed' : 'open';
                $pdo->prepare('UPDATE fiscal_periods SET status = ? WHERE id = ?')->execute([$status, $id]);
                capistra_audit($pdo, $action, 'fiscal_period', $id, 'Status set to ' . $status);
                capistra_flash_set('success', $action === 'close' ? 'Period closed. Posting into it is now blocked.' : 'Period reopened.');
            } else {
                $id = capistra_fiscal_period_save([
                    'id'         => (int) ($_POST['id'] ?? 0),
                    'label'      => capistra_post_string('label'),
                    'start_date' => capistra_post_string('start_date'),
                    'end_date'   => capistra_post_string('end_date'),
                    'status'     => capistra_post_string('status', 'open'),
                ]);
                capistra_audit($pdo, 'save', 'fiscal_period', $id, capistra_post_string('label'));
                capistra_flash_set('success', 'Fiscal period saved.');
            }
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not update period: ' . $e->getMessage());
        }
    }
    header('Location: fiscal.php');
    exit;
}

capistra_layout_header('Settings - Fiscal Periods', 'index.php', '../../');
?>
<h1>Fiscal Periods</h1>
<p class="hint">Closed periods reject new postings. Reopening is explicit and audit-logged.</p>
<?php capistra_render_settings_nav('fiscal.php'); capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th scope="col">Label</th><th scope="col">Start</th><th scope="col">End</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
      <tbody>
      <?php foreach (capistra_fiscal_periods() as $p): ?>
        <tr>
          <th scope="row"><?= e((string) $p['label']) ?></th>
          <td><?= e((string) $p['start_date']) ?></td>
          <td><?= e((string) $p['end_date']) ?></td>
          <td><?= $p['status'] === 'closed' ? '<span class="badge badge--neg">Closed</span>' : '<span class="badge badge--pos">Open</span>' ?></td>
          <td>
            <form method="post" style="display:inline">
              <?= capistra_csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <input type="hidden" name="action" value="<?= $p['status'] === 'closed' ? 'reopen' : 'close' ?>">
              <button class="btn btn--secondary btn--sm" type="submit"><?= $p['status'] === 'closed' ? 'Reopen' : 'Close' ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h2>Add period</h2>
    <form method="post" action="fiscal.php">
      <?= capistra_csrf_field() ?>
      <div class="form-row"><label for="label">Label</label><input id="label" name="label" required placeholder="FY 2026/27"></div>
      <div class="form-row"><label for="start_date">Start date</label><input id="start_date" name="start_date" type="date" required></div>
      <div class="form-row"><label for="end_date">End date</label><input id="end_date" name="end_date" type="date" required></div>
      <div class="form-row"><label for="status">Status</label>
        <select id="status" name="status"><option value="open">Open</option><option value="closed">Closed</option></select></div>
      <button class="btn btn--primary" type="submit">Create period</button>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
