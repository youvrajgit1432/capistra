<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';

capistra_require_login();
$pdo = capistra_pdo();

$accounts = $pdo->query('SELECT id, code, name FROM accounts ORDER BY code')->fetchAll();
$accountId = (int) ($_GET['account_id'] ?? 0);
$from = (string) ($_GET['from'] ?? date('Y-01-01'));
$to   = (string) ($_GET['to'] ?? date('Y-m-d'));

$rows = $accountId > 0 ? ledger_general_ledger($pdo, $accountId, $from, $to) : [];
$selected = null;
foreach ($accounts as $a) { if ((int) $a['id'] === $accountId) { $selected = $a; break; } }

capistra_layout_header('General Ledger', 'general-ledger.php');
?>
<form class="toolbar" method="get" action="general-ledger.php">
  <div class="form-row">
    <label for="account_id">Account</label>
    <select id="account_id" name="account_id" required>
      <option value="">— select account —</option>
      <?php foreach ($accounts as $a): ?>
        <option value="<?= (int) $a['id'] ?>" <?= (int) $a['id'] === $accountId ? 'selected' : '' ?>>
          <?= e($a['code'] . ' · ' . $a['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-row"><label for="from">From</label><input type="date" id="from" name="from" value="<?= e($from) ?>"></div>
  <div class="form-row"><label for="to">To</label><input type="date" id="to" name="to" value="<?= e($to) ?>"></div>
  <button class="btn btn--primary" type="submit">Show</button>
</form>

<?php if ($selected === null): ?>
  <div class="empty-state card">Select an account to view its ledger.</div>
<?php else: ?>
  <h2><?= e($selected['code']) ?> · <?= e($selected['name']) ?></h2>
  <p class="hint"><?= e($from) ?> to <?= e($to) ?></p>
  <div class="table-wrap">
    <table class="data">
      <thead>
        <tr>
          <th scope="col">Date</th><th scope="col">Ref</th><th scope="col">Memo</th>
          <th scope="col" class="num">Debit</th><th scope="col" class="num">Credit</th><th scope="col" class="num">Balance</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($rows === []): ?>
        <tr><td colspan="6" class="empty-state">No transactions in this period.</td></tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><?= e((string) $r['entry_date']) ?></td>
          <td><?= e((string) ($r['reference'] ?? '')) ?></td>
          <td><?= e((string) ($r['memo'] ?? $r['line_memo'] ?? '')) ?></td>
          <td class="num"><?= (float) $r['debit'] ? e(capistra_currency((float) $r['debit'])) : '' ?></td>
          <td class="num"><?= (float) $r['credit'] ? e(capistra_currency((float) $r['credit'])) : '' ?></td>
          <td class="num"><?= capistra_amount((float) $r['running'], false) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php capistra_layout_footer(); ?>
