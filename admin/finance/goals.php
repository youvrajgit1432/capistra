<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
capistra_guard_module('goals');
$pdo = capistra_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            $id = capistra_goal_save([
                'id'                  => (int) ($_POST['id'] ?? 0),
                'name'                => capistra_post_string('name'),
                'target_amount'       => capistra_post_string('target_amount', '0'),
                'current_amount'      => capistra_post_string('current_amount', '0'),
                'target_date'         => capistra_post_string('target_date'),
                'financial_account_id'=> (int) ($_POST['financial_account_id'] ?? 0),
                'status'              => capistra_post_string('status', 'active'),
                'notes'               => capistra_post_string('notes'),
            ]);
            capistra_audit($pdo, 'save', 'goal', $id, capistra_post_string('name'));
            capistra_flash_set('success', 'Goal saved.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save goal: ' . $e->getMessage());
        }
    }
    header('Location: goals.php');
    exit;
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM goals WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}
$goals = capistra_goals();
$accounts = capistra_financial_accounts(true);

capistra_layout_header('Goals', 'goals.php', '../../');
?>
<h1>Financial Goals</h1>
<?php capistra_flash_render(); ?>
<div class="grid grid--3">
  <?php foreach ($goals as $g): ?>
    <div class="card">
      <div class="card__label"><?= e((string) $g['name']) ?> <span class="badge"><?= e((string) $g['status']) ?></span></div>
      <div class="card__value"><?= e(capistra_money((float) $g['current_amount'])) ?></div>
      <div class="card__sub">of <?= e(capistra_money((float) $g['target_amount'])) ?> &middot; <?= e((string) $g['progress']['percent']) ?>%</div>
      <div style="height:12px;background:var(--color-muted);border-radius:999px;margin:var(--space-sm) 0">
        <div style="width:<?= (int) min(100, $g['progress']['percent']) ?>%;height:12px;background:var(--color-positive);border-radius:999px"></div>
      </div>
      <p class="hint">Remaining <?= e(capistra_money((float) $g['progress']['remaining'])) ?>
        <?php if ($g['progress']['months_left'] > 0): ?> &middot; <?= e(capistra_money((float) $g['progress']['required_monthly'])) ?>/month<?php endif; ?>
        <?php if ($g['target_date']): ?> &middot; by <?= e((string) $g['target_date']) ?><?php endif; ?></p>
      <a class="btn btn--secondary btn--sm" href="goals.php?edit=<?= (int) $g['id'] ?>">Edit</a>
    </div>
  <?php endforeach; ?>
  <?php if ($goals === []): ?><div class="card"><p class="empty-state">No goals yet.</p></div><?php endif; ?>
</div>

<div class="card" style="margin-top:var(--space-3xl)">
  <h2><?= $editing ? 'Edit goal' : 'Add goal' ?></h2>
  <form method="post" action="goals.php">
    <?= capistra_csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
    <div class="form-row"><label for="name">Name</label><input id="name" name="name" required value="<?= e((string) ($editing['name'] ?? '')) ?>"></div>
    <div class="form-row"><label for="target_amount">Target amount</label><input id="target_amount" name="target_amount" type="number" step="0.01" required value="<?= e((string) ($editing['target_amount'] ?? '')) ?>"></div>
    <div class="form-row"><label for="current_amount">Current amount</label><input id="current_amount" name="current_amount" type="number" step="0.01" value="<?= e((string) ($editing['current_amount'] ?? '0')) ?>"></div>
    <div class="form-row"><label for="target_date">Target date</label><input id="target_date" name="target_date" type="date" value="<?= e((string) ($editing['target_date'] ?? '')) ?>"></div>
    <div class="form-row"><label for="financial_account_id">Linked account</label>
      <select id="financial_account_id" name="financial_account_id"><option value="0">—</option>
        <?php foreach ($accounts as $a): ?><option value="<?= (int) $a['id'] ?>" <?= (int) ($editing['financial_account_id'] ?? 0) === (int) $a['id'] ? 'selected' : '' ?>><?= e((string) $a['name']) ?></option><?php endforeach; ?>
      </select></div>
    <div class="form-row"><label for="status">Status</label>
      <select id="status" name="status">
        <?php foreach (['active' => 'Active', 'achieved' => 'Achieved', 'paused' => 'Paused', 'cancelled' => 'Cancelled'] as $v => $l): ?>
          <option value="<?= e($v) ?>" <?= ($editing['status'] ?? 'active') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select></div>
    <button class="btn btn--primary" type="submit">Save goal</button>
    <?php if ($editing): ?><a class="btn btn--secondary" href="goals.php">Cancel</a><?php endif; ?>
  </form>
</div>
<?php capistra_layout_footer(); ?>
