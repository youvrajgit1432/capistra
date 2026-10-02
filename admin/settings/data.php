<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
capistra_guard_module('data_import');
$pdo = capistra_pdo();

// ---- Exports ---------------------------------------------------------------
$export = (string) ($_GET['export'] ?? '');
if ($export !== '') {
    $map = [
        'income'      => ['SELECT id, category, date, amount, remarks FROM income ORDER BY date', ['id', 'category', 'date', 'amount', 'remarks']],
        'expenses'    => ['SELECT id, category, date, amount, remarks FROM expenses ORDER BY date', ['id', 'category', 'date', 'amount', 'remarks']],
        'categories'  => ['SELECT type, name, slug, is_active, sort_order FROM transaction_categories ORDER BY type, sort_order', ['type', 'name', 'slug', 'is_active', 'sort_order']],
        'accounts'    => ['SELECT name, type, institution, currency, opening_balance, is_active FROM financial_accounts ORDER BY name', ['name', 'type', 'institution', 'currency', 'opening_balance', 'is_active']],
        'securities'  => ['SELECT symbol, company_name, security_type, listing_status, is_active FROM securities ORDER BY symbol', ['symbol', 'company_name', 'security_type', 'listing_status', 'is_active']],
    ];
    if (isset($map[$export])) {
        [$sql, $cols] = $map[$export];
        $rows = $pdo->query($sql)->fetchAll();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="capistra-' . $export . '.csv"');
        echo capistra_csv_export($rows, $cols);
        exit;
    }
    capistra_flash_set('error', 'Unknown export dataset.');
    header('Location: data.php');
    exit;
}

// ---- Imports ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
        header('Location: data.php');
        exit;
    }
    $action  = capistra_post_string('action');
    $dataset = capistra_post_string('dataset', 'income');
    $type    = $dataset === 'expenses' ? 'expense' : 'income';
    try {
        if ($action === 'preview_import') {
            if (empty($_FILES['csv']['tmp_name']) || !is_uploaded_file($_FILES['csv']['tmp_name'])) {
                throw new RuntimeException('Please choose a CSV file.');
            }
            $parsed = capistra_csv_parse((string) file_get_contents($_FILES['csv']['tmp_name']));
            $result = capistra_csv_validate_transactions($parsed['rows'], $type);
            $_SESSION['_txn_import'][$dataset] = $result['valid'];
            $_SESSION['_txn_import_report'][$dataset] = array_merge($result['errors'], $result['duplicates']);
            capistra_flash_set('info', sprintf('Preview: %d valid, %d errors, %d duplicates.', count($result['valid']), count($result['errors']), count($result['duplicates'])));
        } elseif ($action === 'commit_import') {
            $valid = $_SESSION['_txn_import'][$dataset] ?? [];
            $imported = capistra_csv_import_transactions($valid, $type);
            capistra_log_import($dataset, $dataset . '.csv', count($valid), $imported, 0, 'committed');
            capistra_audit($pdo, 'import', $dataset, null, "Imported $imported $dataset rows from CSV");
            unset($_SESSION['_txn_import'][$dataset], $_SESSION['_txn_import_report'][$dataset]);
            capistra_flash_set('success', "Imported $imported rows. Run Ledger Sync to post them.");
        }
    } catch (Throwable $e) {
        capistra_flash_set('error', 'Import failed: ' . $e->getMessage());
    }
    header('Location: data.php');
    exit;
}

capistra_layout_header('Settings - Import / Export', 'index.php', '../../');
?>
<h1>CSV Import / Export</h1>
<p class="hint">Imports are previewed, validated and duplicate-checked before anything is written. No bank credential integrations are performed.</p>
<?php capistra_render_settings_nav('data.php'); capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="card">
    <h2>Export</h2>
    <p class="hint">Download canonical datasets as UTF-8 CSV.</p>
    <p>
      <a class="btn btn--secondary btn--sm" href="data.php?export=income">Income</a>
      <a class="btn btn--secondary btn--sm" href="data.php?export=expenses">Expenses</a>
      <a class="btn btn--secondary btn--sm" href="data.php?export=categories">Categories</a>
      <a class="btn btn--secondary btn--sm" href="data.php?export=accounts">Financial accounts</a>
      <a class="btn btn--secondary btn--sm" href="data.php?export=securities">Securities</a>
    </p>
  </div>
  <div class="card">
    <h2>Import income / expense transactions</h2>
    <p class="hint">Columns: <code>category,date,amount,account,remarks</code>. Category and account must already exist.</p>
    <form method="post" enctype="multipart/form-data" action="data.php">
      <?= capistra_csrf_field() ?>
      <input type="hidden" name="action" value="preview_import">
      <div class="form-row"><label for="dataset">Dataset</label>
        <select id="dataset" name="dataset">
          <option value="income">Income</option>
          <option value="expenses">Expenses</option>
        </select></div>
      <div class="form-row"><label for="csv">CSV file</label><input id="csv" type="file" name="csv" accept=".csv,text/csv" required></div>
      <button class="btn btn--primary" type="submit">Preview import</button>
    </form>
    <pre class="hint" style="margin-top:var(--space-lg)">category,date,amount,account,remarks
Consulting,2025-10-01,25000,Primary Bank,October retainer</pre>
  </div>
</div>

<?php foreach (['income' => 'Income', 'expenses' => 'Expenses'] as $ds => $label): ?>
  <?php $preview = $_SESSION['_txn_import'][$ds] ?? null; ?>
  <?php if (is_array($preview) && $preview !== []): ?>
    <div class="card" style="margin-top:var(--space-2xl)">
      <h2><?= e($label) ?> import preview (<?= count($preview) ?> rows)</h2>
      <?php foreach (array_slice($_SESSION['_txn_import_report'][$ds] ?? [], 0, 25) as $msg): ?><p class="hint"><?= e($msg) ?></p><?php endforeach; ?>
      <div class="table-wrap" style="max-height:300px;overflow:auto">
        <table class="data">
          <thead><tr><th scope="col">Category</th><th scope="col">Date</th><th scope="col" class="num">Amount</th><th scope="col">Remarks</th></tr></thead>
          <tbody>
          <?php foreach (array_slice($preview, 0, 100) as $row): ?>
            <tr><td><?= e($row['category']) ?></td><td><?= e($row['date']) ?></td><td class="num"><?= e(capistra_money((float) $row['amount'])) ?></td><td><?= e($row['remarks']) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <form method="post" style="margin-top:var(--space-lg)">
        <?= capistra_csrf_field() ?>
        <input type="hidden" name="action" value="commit_import">
        <input type="hidden" name="dataset" value="<?= e($ds) ?>">
        <button class="btn btn--primary" type="submit">Commit import</button>
      </form>
    </div>
  <?php endif; ?>
<?php endforeach; ?>
<?php capistra_layout_footer(); ?>
