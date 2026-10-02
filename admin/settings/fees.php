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
            $id = (int) ($_POST['id'] ?? 0);
            $params = [
                ':name' => capistra_post_string('name'),
                ':ctx'  => capistra_post_string('transaction_context', 'buy'),
                ':calc' => capistra_post_string('calculation_type', 'percentage') === 'fixed' ? 'fixed' : 'percentage',
                ':rate' => capistra_post_string('rate') !== '' ? (float) $_POST['rate'] : null,
                ':fixed'=> capistra_post_string('fixed_amount') !== '' ? (float) $_POST['fixed_amount'] : null,
                ':min'  => capistra_post_string('min_amount') !== '' ? (float) $_POST['min_amount'] : null,
                ':max'  => capistra_post_string('max_amount') !== '' ? (float) $_POST['max_amount'] : null,
                ':ef'   => capistra_post_string('effective_from') !== '' ? capistra_post_string('effective_from') : null,
                ':et'   => capistra_post_string('effective_to') !== '' ? capistra_post_string('effective_to') : null,
                ':active' => !empty($_POST['is_active']) ? 1 : 0,
                ':notes'  => capistra_post_string('notes'),
            ];
            if ($id > 0) {
                $params[':id'] = $id;
                $pdo->prepare('UPDATE fee_rules SET name=:name, transaction_context=:ctx, calculation_type=:calc,
                        rate=:rate, fixed_amount=:fixed, min_amount=:min, max_amount=:max,
                        effective_from=:ef, effective_to=:et, is_active=:active, notes=:notes WHERE id=:id')->execute($params);
            } else {
                $pdo->prepare('INSERT INTO fee_rules (name, transaction_context, calculation_type, rate, fixed_amount,
                        min_amount, max_amount, effective_from, effective_to, is_active, notes)
                    VALUES (:name, :ctx, :calc, :rate, :fixed, :min, :max, :ef, :et, :active, :notes)')->execute($params);
                $id = (int) $pdo->lastInsertId();
            }
            capistra_audit($pdo, 'save', 'fee_rule', $id, capistra_post_string('name'));
            capistra_flash_set('success', 'Fee rule saved.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save fee rule: ' . $e->getMessage());
        }
    }
    header('Location: fees.php');
    exit;
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM fee_rules WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}
$rules = capistra_fee_rules(null, false);

capistra_layout_header('Settings - Fees & Taxes', 'index.php', '../../');
?>
<h1>Fees &amp; Taxes</h1>
<div class="alert alert--info">Demo rules are illustrations only. Regulatory values change — verify from an authoritative source before relying on them, and set the effective dates accordingly.</div>
<?php capistra_render_settings_nav('fees.php'); capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th scope="col">Name</th><th scope="col">Context</th><th scope="col">Calculation</th><th scope="col">Effective</th><th scope="col">Active</th><th scope="col">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rules as $r): ?>
        <tr>
          <th scope="row"><?= e((string) $r['name']) ?></th>
          <td><?= e((string) $r['transaction_context']) ?></td>
          <td><?= $r['calculation_type'] === 'fixed' ? e(capistra_money((float) $r['fixed_amount'])) : e((string) $r['rate'] . '%') ?></td>
          <td><?= e((string) ($r['effective_from'] ?? '—')) ?><?= $r['effective_to'] ? ' → ' . e((string) $r['effective_to']) : '' ?></td>
          <td><?= $r['is_active'] ? 'Yes' : 'No' ?></td>
          <td><a class="btn btn--secondary btn--sm" href="fees.php?edit=<?= (int) $r['id'] ?>">Edit</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h2><?= $editing ? 'Edit rule' : 'Add rule' ?></h2>
    <form method="post" action="fees.php">
      <?= capistra_csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
      <div class="form-row"><label for="name">Name</label><input id="name" name="name" required maxlength="150" value="<?= e((string) ($editing['name'] ?? '')) ?>"></div>
      <div class="form-row"><label for="transaction_context">Context</label>
        <select id="transaction_context" name="transaction_context">
          <?php foreach (['buy' => 'Buy', 'sell' => 'Sell', 'dividend' => 'Dividend', 'transfer' => 'Transfer', 'other' => 'Other'] as $v => $l): ?>
            <option value="<?= e($v) ?>" <?= ($editing['transaction_context'] ?? 'buy') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="calculation_type">Calculation</label>
        <select id="calculation_type" name="calculation_type">
          <option value="percentage" <?= ($editing['calculation_type'] ?? 'percentage') === 'percentage' ? 'selected' : '' ?>>Percentage</option>
          <option value="fixed" <?= ($editing['calculation_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed amount</option>
        </select></div>
      <div class="form-row"><label for="rate">Rate (%)</label><input id="rate" name="rate" type="number" step="0.0001" value="<?= e((string) ($editing['rate'] ?? '')) ?>"></div>
      <div class="form-row"><label for="fixed_amount">Fixed amount</label><input id="fixed_amount" name="fixed_amount" type="number" step="0.01" value="<?= e((string) ($editing['fixed_amount'] ?? '')) ?>"></div>
      <div class="form-row"><label for="min_amount">Minimum</label><input id="min_amount" name="min_amount" type="number" step="0.01" value="<?= e((string) ($editing['min_amount'] ?? '')) ?>"></div>
      <div class="form-row"><label for="max_amount">Maximum</label><input id="max_amount" name="max_amount" type="number" step="0.01" value="<?= e((string) ($editing['max_amount'] ?? '')) ?>"></div>
      <div class="form-row"><label for="effective_from">Effective from</label><input id="effective_from" name="effective_from" type="date" value="<?= e((string) ($editing['effective_from'] ?? '')) ?>"></div>
      <div class="form-row"><label for="effective_to">Effective to</label><input id="effective_to" name="effective_to" type="date" value="<?= e((string) ($editing['effective_to'] ?? '')) ?>"></div>
      <div class="form-row"><label class="switch"><input type="checkbox" name="is_active" value="1" <?= (($editing['is_active'] ?? 1) ? 'checked' : '') ?>><span>Active</span></label></div>
      <button class="btn btn--primary" type="submit">Save rule</button>
      <?php if ($editing): ?><a class="btn btn--secondary" href="fees.php">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
