<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/layout.php';

capistra_require_login();
$pdo = capistra_pdo();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security token mismatch. Please try again.';
    } else {
        $code  = trim((string) ($_POST['code'] ?? ''));
        $name  = trim((string) ($_POST['name'] ?? ''));
        $type  = (string) ($_POST['type'] ?? '');
        $validTypes = ['asset', 'liability', 'equity', 'income', 'expense'];

        if ($code === '' || $name === '') {
            $errors[] = 'Code and name are required.';
        }
        if (!in_array($type, $validTypes, true)) {
            $errors[] = 'Invalid account type.';
        }
        if ($errors === []) {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO accounts (code, name, type, opening_balance, opening_balance_type, is_active, description)
                     VALUES (:code, :name, :type, 0, \'debit\', 1, NULL)'
                );
                $stmt->execute([':code' => $code, ':name' => $name, ':type' => $type]);
                capistra_audit($pdo, 'create', 'account', (int) $pdo->lastInsertId(), "Created account $code");
                header('Location: chart-of-accounts.php?created=1');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Could not create account (duplicate code?).';
            }
        }
    }
}

$balances = ledger_account_balances($pdo);
$byType = [];
foreach ($balances as $acc) {
    $byType[$acc['type']][] = $acc;
}
$labels = [
    'asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity',
    'income' => 'Income', 'expense' => 'Expenses',
];

capistra_layout_header('Chart of Accounts', 'chart-of-accounts.php');
?>
<?php if (isset($_GET['created'])): ?><div class="alert alert--success">Account created.</div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert--error"><?= e($e) ?></div><?php endforeach; ?>

<div class="grid grid--2">
  <div>
    <?php foreach ($labels as $type => $label): ?>
      <h2><?= e($label) ?></h2>
      <div class="table-wrap" style="margin-bottom:var(--space-2xl)">
        <table class="data">
          <thead>
            <tr><th scope="col">Code</th><th scope="col">Name</th><th scope="col" class="num">Balance</th></tr>
          </thead>
          <tbody>
          <?php if (empty($byType[$type])): ?>
            <tr><td colspan="3" class="empty-state">No accounts.</td></tr>
          <?php else: foreach ($byType[$type] as $acc): ?>
            <tr>
              <td><?= e($acc['code']) ?></td>
              <td><?= e($acc['name']) ?></td>
              <td class="num"><?= capistra_amount($acc['balance'], false) ?></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  </div>

  <div>
    <div class="card">
      <h2>Add account</h2>
      <form method="post" action="chart-of-accounts.php">
        <?= capistra_csrf_field() ?>
        <div class="form-row">
          <label for="code">Code</label>
          <input id="code" name="code" required maxlength="20" inputmode="numeric" placeholder="e.g. 1300">
        </div>
        <div class="form-row">
          <label for="name">Name</label>
          <input id="name" name="name" required maxlength="150" placeholder="e.g. Prepaid Expenses">
        </div>
        <div class="form-row">
          <label for="type">Type</label>
          <select id="type" name="type" required>
            <?php foreach ($labels as $type => $label): ?>
              <option value="<?= e($type) ?>"><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn btn--primary" type="submit">Create account</button>
      </form>
    </div>
  </div>
</div>
<?php capistra_layout_footer(); ?>
