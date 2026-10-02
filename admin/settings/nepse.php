<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
$pdo  = capistra_pdo();
capistra_default_exchange_id();
$exchangeId = capistra_default_exchange_id();

// ---- CSV export ------------------------------------------------------------
if (($_GET['export'] ?? '') === 'csv') {
    $rows = capistra_securities();
    $csv = capistra_csv_export($rows, ['symbol', 'company_name', 'sector_name', 'security_type', 'listing_status', 'is_active']);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="nepse-securities.csv"');
    echo $csv;
    exit;
}

// ---- POST ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
        header('Location: nepse.php');
        exit;
    }
    $action = capistra_post_string('action', 'save');
    try {
        if ($action === 'save') {
            $id = capistra_security_save([
                'id'           => (int) ($_POST['id'] ?? 0),
                'exchange_id'  => $exchangeId,
                'sector_id'    => (int) ($_POST['sector_id'] ?? 0),
                'symbol'       => capistra_post_string('symbol'),
                'company_name' => capistra_post_string('company_name'),
                'security_type'=> capistra_post_string('security_type', 'equity'),
                'face_value'   => capistra_post_string('face_value'),
                'listing_status' => capistra_post_string('listing_status', 'unverified'),
                'listing_date' => capistra_post_string('listing_date'),
                'delisted_date' => capistra_post_string('delisted_date'),
                'market_data_symbol' => capistra_post_string('market_data_symbol'),
                'notes'        => capistra_post_string('notes'),
                'is_active'    => !empty($_POST['is_active']),
            ]);
            capistra_audit($pdo, 'save', 'security', $id, capistra_post_string('symbol'));
            capistra_flash_set('success', 'Security saved.');
        } elseif ($action === 'toggle') {
            $id = (int) ($_POST['id'] ?? 0);
            $pdo->prepare('UPDATE securities SET is_active = ? WHERE id = ?')->execute([!empty($_POST['active']) ? 1 : 0, $id]);
            capistra_audit($pdo, 'update', 'security', $id, 'active state changed');
            capistra_flash_set('success', 'Security updated.');
        } elseif ($action === 'preview_import') {
            if (empty($_FILES['csv']['tmp_name']) || !is_uploaded_file($_FILES['csv']['tmp_name'])) {
                throw new RuntimeException('Please choose a CSV file.');
            }
            $parsed = capistra_csv_parse((string) file_get_contents($_FILES['csv']['tmp_name']));
            $result = capistra_csv_validate_securities($parsed['rows']);
            $_SESSION['_sec_import'] = $result['valid'];
            capistra_flash_set('info', sprintf('Preview: %d valid, %d errors, %d duplicates.', count($result['valid']), count($result['errors']), count($result['duplicates'])));
            $_SESSION['_sec_import_report'] = array_merge($result['errors'], $result['duplicates']);
        } elseif ($action === 'commit_import') {
            $valid = $_SESSION['_sec_import'] ?? [];
            $imported = capistra_csv_import_securities($valid);
            capistra_log_import('securities', 'securities.csv', count($valid), $imported, 0, 'committed');
            capistra_audit($pdo, 'import', 'security', null, "Imported $imported securities from CSV");
            unset($_SESSION['_sec_import'], $_SESSION['_sec_import_report']);
            capistra_flash_set('success', "Imported $imported securities (all marked unverified).");
        }
    } catch (Throwable $e) {
        capistra_flash_set('error', 'Import failed: ' . $e->getMessage());
    }
    header('Location: nepse.php');
    exit;
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM securities WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}
$filters = [
    'q'       => trim((string) ($_GET['q'] ?? '')),
    'sector_id' => (int) ($_GET['sector_id'] ?? 0),
    'status'  => trim((string) ($_GET['status'] ?? '')),
];
$securities = capistra_securities($filters);
$preview = $_SESSION['_sec_import'] ?? null;
$previewReport = $_SESSION['_sec_import_report'] ?? [];

capistra_layout_header('Settings - Securities', 'index.php', '../../');
?>
<h1>NEPSE Securities</h1>
<p class="hint">No source-code editing is needed to add a newly listed company. Imported rows are marked <strong>unverified</strong> until confirmed.</p>
<?php capistra_render_settings_nav('nepse.php'); capistra_flash_render(); ?>

<div class="card">
  <h2>Filter &amp; export</h2>
  <form method="get" action="nepse.php" class="grid grid--2">
    <div class="form-row"><label for="q">Search</label><input id="q" name="q" value="<?= e($filters['q']) ?>" placeholder="Symbol or company"></div>
    <div class="form-row"><label for="sector_id">Sector</label>
      <select id="sector_id" name="sector_id">
        <option value="0">All sectors</option>
        <?php foreach (capistra_sectors($exchangeId) as $s): ?>
          <option value="<?= (int) $s['id'] ?>" <?= $filters['sector_id'] === (int) $s['id'] ? 'selected' : '' ?>><?= e((string) $s['name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="form-row"><label for="status">Status</label>
      <select id="status" name="status">
        <option value="">All statuses</option>
        <?php foreach (capistra_listing_statuses() as $v => $l): ?>
          <option value="<?= e($v) ?>" <?= $filters['status'] === $v ? 'selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="form-row"><button class="btn btn--primary btn--sm" type="submit">Apply</button>
      <a class="btn btn--secondary btn--sm" href="nepse.php?export=csv">Export CSV</a></div>
  </form>
</div>

<?php if (is_array($preview)): ?>
  <div class="card" style="margin-top:var(--space-2xl)">
    <h2>Import preview (<?= count($preview) ?> rows)</h2>
    <?php if ($previewReport): ?><ul class="hint"><?php foreach (array_slice($previewReport, 0, 25) as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <div class="table-wrap" style="max-height:320px;overflow:auto">
      <table class="data">
        <thead><tr><th scope="col">Symbol</th><th scope="col">Company</th><th scope="col">Type</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($preview, 0, 100) as $row): ?>
            <tr><td><?= e($row['symbol']) ?></td><td><?= e($row['company_name']) ?></td><td><?= e($row['security_type']) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <form method="post" style="margin-top:var(--space-lg)">
      <?= capistra_csrf_field() ?>
      <input type="hidden" name="action" value="commit_import">
      <button class="btn btn--primary" type="submit">Commit import</button>
    </form>
  </div>
<?php endif; ?>

<h2 style="margin-top:var(--space-3xl)">Securities (<?= count($securities) ?>)</h2>
<div class="table-wrap" style="max-height:520px;overflow:auto">
  <table class="data">
    <thead><tr><th scope="col">Symbol</th><th scope="col">Company</th><th scope="col">Sector</th><th scope="col">Type</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($securities as $s): ?>
      <tr>
        <th scope="row"><?= e((string) $s['symbol']) ?></th>
        <td><?= e((string) $s['company_name']) ?></td>
        <td><?= $s['sector_name'] ? e((string) $s['sector_name']) : '—' ?></td>
        <td><?= e(capistra_security_types()[$s['security_type']] ?? (string) $s['security_type']) ?></td>
        <td><span class="badge <?= $s['listing_status'] === 'listed' ? 'badge--pos' : ($s['listing_status'] === 'delisted' ? 'badge--neg' : '') ?>"><?= e(capistra_listing_statuses()[$s['listing_status']] ?? (string) $s['listing_status']) ?></span>
          <?php if (!$s['is_active']): ?> <span class="badge">Inactive</span><?php endif; ?></td>
        <td><a class="btn btn--secondary btn--sm" href="nepse.php?edit=<?= (int) $s['id'] ?>">Edit</a>
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

<div class="grid grid--2" style="margin-top:var(--space-3xl)">
  <div class="card">
    <h2><?= $editing ? 'Edit security' : 'Add security' ?></h2>
    <form method="post" action="nepse.php">
      <?= capistra_csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
      <div class="form-row"><label for="symbol">Symbol</label><input id="symbol" name="symbol" required maxlength="30" value="<?= e((string) ($editing['symbol'] ?? '')) ?>"></div>
      <div class="form-row"><label for="company_name">Company name</label><input id="company_name" name="company_name" required maxlength="255" value="<?= e((string) ($editing['company_name'] ?? '')) ?>"></div>
      <div class="form-row"><label for="sector_id_e">Sector</label>
        <select id="sector_id_e" name="sector_id">
          <option value="">Unclassified</option>
          <?php foreach (capistra_sectors($exchangeId) as $s): ?>
            <option value="<?= (int) $s['id'] ?>" <?= (int) ($editing['sector_id'] ?? 0) === (int) $s['id'] ? 'selected' : '' ?>><?= e((string) $s['name']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="security_type">Type</label>
        <select id="security_type" name="security_type">
          <?php foreach (capistra_security_types() as $v => $l): ?>
            <option value="<?= e($v) ?>" <?= ($editing['security_type'] ?? 'equity') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="listing_status">Listing status</label>
        <select id="listing_status" name="listing_status">
          <?php foreach (capistra_listing_statuses() as $v => $l): ?>
            <option value="<?= e($v) ?>" <?= ($editing['listing_status'] ?? 'unverified') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="face_value">Face value</label><input id="face_value" name="face_value" type="number" step="0.01" value="<?= e((string) ($editing['face_value'] ?? '')) ?>"></div>
      <div class="form-row"><label for="market_data_symbol">Market-data symbol</label><input id="market_data_symbol" name="market_data_symbol" maxlength="30" value="<?= e((string) ($editing['market_data_symbol'] ?? '')) ?>"></div>
      <div class="form-row"><label class="switch"><input type="checkbox" name="is_active" value="1" <?= (($editing['is_active'] ?? 1) ? 'checked' : '') ?>><span>Active</span></label></div>
      <button class="btn btn--primary" type="submit">Save security</button>
      <?php if ($editing): ?><a class="btn btn--secondary" href="nepse.php">Cancel</a><?php endif; ?>
    </form>
  </div>
  <div class="card">
    <h2>Bulk CSV import</h2>
    <p class="hint">Required columns: <code>symbol</code>, <code>company_name</code>. Optional: <code>sector</code> (slug/name), <code>security_type</code>. Duplicates are detected and never overwritten.</p>
    <form method="post" enctype="multipart/form-data" action="nepse.php">
      <?= capistra_csrf_field() ?>
      <input type="hidden" name="action" value="preview_import">
      <div class="form-row"><label for="csv">CSV file</label><input id="csv" type="file" name="csv" accept=".csv,text/csv" required></div>
      <button class="btn btn--primary" type="submit">Preview import</button>
    </form>
    <p class="hint" style="margin-top:var(--space-lg)">Template:</p>
    <pre class="hint">symbol,company_name,sector,security_type
EXHYD,Example Hydro Ltd,hydropower,equity</pre>
  </div>
</div>
<?php capistra_layout_footer(); ?>
