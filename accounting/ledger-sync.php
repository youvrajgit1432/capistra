<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';
require_once __DIR__ . '/lib/integrate.php';

$user = capistra_require_login();
$pdo  = capistra_pdo();

$result = null;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security token mismatch. Please try again.';
    } else {
        try {
            $result = capistra_sync_ledger($pdo);
            capistra_audit($pdo, 'sync', 'ledger', null, sprintf(
                'Posted %d income and %d expense entries',
                $result['income'],
                $result['expense']
            ));
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$pendingIncome  = (int) $pdo->query('SELECT COUNT(*) FROM income   WHERE journal_entry_id IS NULL')->fetchColumn();
$pendingExpense = (int) $pdo->query('SELECT COUNT(*) FROM expenses WHERE journal_entry_id IS NULL')->fetchColumn();

capistra_layout_header('Ledger Sync', 'ledger-sync.php');
?>
<?php foreach ($errors as $e): ?><div class="alert alert--error"><?= e($e) ?></div><?php endforeach; ?>
<?php if ($result !== null): ?>
  <div class="alert alert--success">
    Posted <?= (int) $result['income'] ?> income and <?= (int) $result['expense'] ?> expense entries to the journal.
  </div>
<?php endif; ?>

<div class="card">
  <h2>Income &amp; expense to ledger</h2>
  <p class="hint">
    This posts each unposted income and expense record into the double-entry journal
    (Cash&nbsp;/&nbsp;relevant income or expense account) and links the record to its
    journal entry. It is idempotent — running it again never double-posts.
  </p>
  <table class="data" style="margin:var(--space-xl) 0">
    <thead><tr><th scope="col">Source</th><th scope="col" class="num">Awaiting posting</th></tr></thead>
    <tbody>
      <tr><th scope="row">Income records</th><td class="num"><?= $pendingIncome ?></td></tr>
      <tr><th scope="row">Expense records</th><td class="num"><?= $pendingExpense ?></td></tr>
    </tbody>
  </table>
  <form method="post" action="ledger-sync.php">
    <?= capistra_csrf_field() ?>
    <button class="btn btn--primary" type="submit">Post unposted records to the journal</button>
  </form>
</div>
<?php capistra_layout_footer(); ?>
