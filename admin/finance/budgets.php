<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
capistra_guard_module('budgeting');
$pdo = capistra_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            $items = [];
            foreach ((array) ($_POST['item'] ?? []) as $categoryId => $amount) {
                $items[] = ['category_id' => (int) $categoryId, 'amount' => (float) $amount];
            }
            $id = capistra_budget_save([
                'id'           => (int) ($_POST['id'] ?? 0),
                'name'         => capistra_post_string('name'),
                'period_year'  => (int) ($_POST['period_year'] ?? date('Y')),
                'period_month' => (int) ($_POST['period_month'] ?? date('n')),
                'notes'        => capistra_post_string('notes'),
                'items'        => $items,
            ]);
            capistra_audit($pdo, 'save', 'budget', $id, capistra_post_string('name'));
            capistra_flash_set('success', 'Budget saved.');
            header('Location: budgets.php?id=' . $id);
            exit;
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save budget: ' . $e->getMessage());
        }
    }
    header('Location: budgets.php');
    exit;
}

$budgets = capistra_budgets();
$selectedId = (int) ($_GET['id'] ?? ($budgets[0]['id'] ?? 0));
$report = $selectedId > 0 ? capistra_budget_report($selectedId) : null;
$expenseCategories = capistra_categories('expense', true);

capistra_layout_header('Budgets', 'budgets.php', '../../');
?>
<h1>Budgets</h1>
<p class="hint">Monthly category limits compared with actual expenses.</p>
<?php capistra_flash_render(); ?>

<div class="grid grid--2">
  <div>
    <?php if ($report && $report['budget']): ?>
      <div class="card">
        <h2><?= e((string) $report['budget']['name']) ?></h2>
        <table class="data">
          <thead><tr><th scope="col">Category</th><th scope="col" class="num">Budget</th><th scope="col" class="num">Actual</th><th scope="col" class="num">Remaining</th><th scope="col" class="num">Used</th></tr></thead>
          <tbody>
          <?php foreach ($report['rows'] as $row): ?>
            <tr>
              <th scope="row"><?= e((string) $row['category']) ?><?php if ($row['over']): ?> <span class="badge badge--neg">Over</span><?php endif; ?></th>
              <td class="num"><?= e(capistra_money((float) $row['budget'])) ?></td>
              <td class="num"><?= e(capistra_money((float) $row['actual'])) ?></td>
              <td class="num"><?= capistra_amount((float) $row['remaining']) ?></td>
              <td class="num"><?= e((string) $row['percent']) ?>%</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot><tr><td>Total</td><td class="num"><?= e(capistra_money($report['totals']['budget'])) ?></td><td class="num"><?= e(capistra_money($report['totals']['actual'])) ?></td><td class="num"><?= capistra_amount($report['totals']['remaining']) ?></td><td class="num"><?= e((string) $report['totals']['percent']) ?>%</td></tr></tfoot>
        </table>
      </div>
    <?php endif; ?>
    <div class="card" style="margin-top:var(--space-2xl)">
      <h2>Existing budgets</h2>
      <table class="data">
        <tbody>
        <?php foreach ($budgets as $b): ?>
          <tr><th scope="row"><a href="budgets.php?id=<?= (int) $b['id'] ?>"><?= e((string) $b['name']) ?></a></th><td class="num"><?= e(capistra_money((float) $b['total'])) ?></td></tr>
        <?php endforeach; ?>
        <?php if ($budgets === []): ?><tr><td class="empty-state">No budgets yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <h2>Create / update budget</h2>
    <form method="post" action="budgets.php">
      <?= capistra_csrf_field() ?>
      <input type="hidden" name="id" value="0">
      <div class="form-row"><label for="name">Name</label><input id="name" name="name" placeholder="Budget 2025-11"></div>
      <div class="form-row"><label for="period_year">Year</label><input id="period_year" name="period_year" type="number" value="<?= date('Y') ?>" required></div>
      <div class="form-row"><label for="period_month">Month</label>
        <select id="period_month" name="period_month">
          <?php for ($m = 1; $m <= 12; $m++): ?><option value="<?= $m ?>" <?= (int) date('n') === $m ? 'selected' : '' ?>><?= e(date('F', mktime(0, 0, 0, $m, 1))) ?></option><?php endfor; ?>
        </select></div>
      <h3>Category limits</h3>
      <?php foreach ($expenseCategories as $c): ?>
        <div class="form-row"><label for="item_<?= (int) $c['id'] ?>"><?= e((string) $c['name']) ?></label>
          <input id="item_<?= (int) $c['id'] ?>" name="item[<?= (int) $c['id'] ?>]" type="number" step="0.01" value="0"></div>
      <?php endforeach; ?>
      <button class="btn btn--primary" type="submit">Save budget</button>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
