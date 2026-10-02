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
            $id = (int) ($_POST['category_id'] ?? 0);
            $ledger = (int) ($_POST['ledger_account_id'] ?? 0);
            $pdo->prepare('UPDATE transaction_categories SET ledger_account_id = ? WHERE id = ?')->execute([$ledger > 0 ? $ledger : null, $id]);
            capistra_audit($pdo, 'update', 'category_mapping', $id, 'ledger account ' . $ledger);
            capistra_flash_set('success', 'Ledger mapping updated.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not update mapping: ' . $e->getMessage());
        }
    }
    header('Location: accounting.php');
    exit;
}

$accounts = $pdo->query("SELECT id, code, name, type FROM accounts WHERE type IN ('income','expense') ORDER BY code")->fetchAll();
$unmapped = array_filter(capistra_categories(), static fn($c) => empty($c['ledger_account_id']));

capistra_layout_header('Settings - Accounting', 'index.php', '../../');
?>
<h1>Accounting Integration</h1>
<p class="hint">Category → ledger mappings drive every posting. Fallback accounts (4000 / 5900) apply only to legacy records with no mapping.</p>
<?php capistra_render_settings_nav('accounting.php'); capistra_flash_render(); ?>

<?php if ($unmapped): ?>
  <div class="alert alert--info"><?= count($unmapped) ?> categor<?= count($unmapped) === 1 ? 'y is' : 'ies are' ?> unmapped and will use the fallback account.</div>
<?php endif; ?>

<div class="grid grid--2">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th scope="col">Category</th><th scope="col">Type</th><th scope="col">Ledger account</th></tr></thead>
      <tbody>
      <?php foreach (capistra_categories() as $cat): ?>
        <tr>
          <th scope="row"><?= e((string) $cat['name']) ?></th>
          <td><?= e((string) $cat['type']) ?></td>
          <td>
            <form method="post" style="display:flex;gap:var(--space-sm);align-items:center">
              <?= capistra_csrf_field() ?>
              <input type="hidden" name="category_id" value="<?= (int) $cat['id'] ?>">
              <select name="ledger_account_id">
                <option value="">(fallback)</option>
                <?php foreach ($accounts as $a): ?>
                  <?php if ($a['type'] === $cat['type']): ?>
                    <option value="<?= (int) $a['id'] ?>" <?= (int) $cat['ledger_account_id'] === (int) $a['id'] ? 'selected' : '' ?>><?= e($a['code'] . ' · ' . $a['name']) ?></option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </select>
              <button class="btn btn--secondary btn--sm" type="submit">Set</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div>
    <div class="card">
      <h2>Chart of accounts</h2>
      <p class="hint">Add or review ledger accounts used by the mappings above.</p>
      <a class="btn btn--primary" href="<?= e(capistra_url('accounting/chart-of-accounts.php')) ?>">Open Chart of Accounts</a>
    </div>
    <div class="card" style="margin-top:var(--space-xl)">
      <h2>Ledger sync</h2>
      <p class="hint">Post any income/expense records that are not yet in the journal.</p>
      <a class="btn btn--secondary" href="<?= e(capistra_url('accounting/ledger-sync.php')) ?>">Open Income/Expense Sync</a>
    </div>
  </div>
</div>
<?php capistra_layout_footer(); ?>
