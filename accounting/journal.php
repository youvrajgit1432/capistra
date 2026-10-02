<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';

$user = capistra_require_login();
$pdo  = capistra_pdo();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security token mismatch. Please try again.';
    } else {
        $date  = (string) ($_POST['entry_date'] ?? '');
        $memo  = trim((string) ($_POST['memo'] ?? ''));
        $ids   = $_POST['account_id'] ?? [];
        $debits = $_POST['debit'] ?? [];
        $credits = $_POST['credit'] ?? [];

        $lines = [];
        foreach ((array) $ids as $i => $accId) {
            $d = (float) ($debits[$i] ?? 0);
            $c = (float) ($credits[$i] ?? 0);
            if ((int) $accId > 0 && ($d > 0 || $c > 0)) {
                $lines[] = ['account_id' => (int) $accId, 'debit' => $d, 'credit' => $c];
            }
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'A valid entry date is required.';
        }
        try {
            $entryId = ledger_post_entry($pdo, [
                'entry_date'  => $date,
                'memo'        => $memo,
                'source_type' => 'manual',
                'created_by'  => $user['id'],
            ], $lines);
            capistra_audit($pdo, 'post', 'journal_entry', $entryId, 'Manual journal entry');
            header('Location: journal.php?posted=' . $entryId);
            exit;
        } catch (Throwable $ex) {
            $errors[] = $ex->getMessage();
        }
    }
}

$accounts = $pdo->query('SELECT id, code, name, type FROM accounts WHERE is_active = 1 ORDER BY code')->fetchAll();
$entries = $pdo->query('SELECT id, entry_date, reference, memo, source_type, status
                        FROM journal_entries ORDER BY entry_date DESC, id DESC LIMIT 40')->fetchAll();

capistra_layout_header('Journal Entries', 'journal.php');
?>
<?php if (isset($_GET['posted'])): ?><div class="alert alert--success">Journal entry #<?= (int) $_GET['posted'] ?> posted.</div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert--error"><?= e($e) ?></div><?php endforeach; ?>

<div class="card" style="margin-bottom:var(--space-2xl)">
  <h2>New journal entry</h2>
  <p class="hint">Every entry must balance: total debit must equal total credit.</p>
  <form method="post" action="journal.php">
    <?= capistra_csrf_field() ?>
    <div class="toolbar">
      <div class="form-row">
        <label for="entry_date">Date</label>
        <input type="date" id="entry_date" name="entry_date" value="<?= e(date('Y-m-d')) ?>" required>
      </div>
      <div class="form-row" style="flex:1 1 320px">
        <label for="memo">Memo</label>
        <input id="memo" name="memo" maxlength="255" placeholder="Description of this entry">
      </div>
    </div>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th scope="col">Account</th><th scope="col" class="num">Debit</th><th scope="col" class="num">Credit</th></tr>
        </thead>
        <tbody>
        <?php for ($i = 0; $i < 4; $i++): ?>
          <tr>
            <td>
              <label class="sr-only" for="account_id_<?= $i ?>">Account <?= $i + 1 ?></label>
              <select id="account_id_<?= $i ?>" name="account_id[]">
                <option value="">— select account —</option>
                <?php foreach ($accounts as $a): ?>
                  <option value="<?= (int) $a['id'] ?>"><?= e($a['code'] . ' · ' . $a['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td class="num"><input name="debit[]" inputmode="decimal" value="" aria-label="Debit row <?= $i + 1 ?>"></td>
            <td class="num"><input name="credit[]" inputmode="decimal" value="" aria-label="Credit row <?= $i + 1 ?>"></td>
          </tr>
        <?php endfor; ?>
        </tbody>
      </table>
    </div>
    <p style="margin-top:var(--space-xl)"><button class="btn btn--primary" type="submit">Post entry</button></p>
  </form>
</div>

<h2>Recent entries</h2>
<div class="table-wrap">
  <table class="data">
    <thead><tr><th scope="col">#</th><th scope="col">Date</th><th scope="col">Memo</th><th scope="col">Source</th><th scope="col">Status</th></tr></thead>
    <tbody>
    <?php if ($entries === []): ?>
      <tr><td colspan="5" class="empty-state">No journal entries yet.</td></tr>
    <?php else: foreach ($entries as $en): ?>
      <tr>
        <td>#<?= (int) $en['id'] ?></td>
        <td><?= e((string) $en['entry_date']) ?></td>
        <td><?= e((string) ($en['memo'] ?? '')) ?></td>
        <td><span class="badge"><?= e((string) $en['source_type']) ?></span></td>
        <td><?= e((string) $en['status']) ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?php capistra_layout_footer(); ?>
