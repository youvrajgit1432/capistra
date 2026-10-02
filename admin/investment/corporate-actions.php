<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
capistra_guard_module('stocks');
$pdo = capistra_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            $id = capistra_apply_corporate_action([
                'security_id'    => (int) ($_POST['security_id'] ?? 0),
                'portfolio_id'   => (int) ($_POST['portfolio_id'] ?? 0),
                'action_type'    => capistra_post_string('action_type', 'cash_dividend'),
                'action_date'    => capistra_post_string('action_date', date('Y-m-d')),
                'ratio_from'     => capistra_post_string('ratio_from'),
                'ratio_to'       => capistra_post_string('ratio_to'),
                'dividend_per_share' => capistra_post_string('dividend_per_share'),
                'units'          => capistra_post_string('units', '0'),
                'price_per_unit' => capistra_post_string('price_per_unit', '0'),
                'amount'         => capistra_post_string('amount', '0'),
                'notes'          => capistra_post_string('notes'),
            ]);
            capistra_audit($pdo, 'create', 'corporate_action', $id, capistra_post_string('action_type'));
            capistra_flash_set('success', 'Corporate action applied and reflected in holdings.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not apply action: ' . $e->getMessage());
        }
    }
    header('Location: corporate-actions.php');
    exit;
}

$actions = capistra_corporate_actions();
$securities = capistra_securities(['active_only' => true]);
$portfolios = capistra_portfolios(true);

capistra_layout_header('Corporate Actions', 'corporate-actions.php', '../../');
?>
<h1>Corporate Actions</h1>
<p class="hint">Cash dividends, bonus shares, rights, splits and merger adjustments. Each action materialises a stock transaction so holdings stay correct — you never fake these as ordinary buys.</p>
<?php capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th scope="col">Date</th><th scope="col">Symbol</th><th scope="col">Type</th><th scope="col">Detail</th></tr></thead>
      <tbody>
      <?php foreach ($actions as $a): ?>
        <tr>
          <th scope="row"><?= e((string) $a['action_date']) ?></th>
          <td><?= e((string) $a['symbol']) ?></td>
          <td><?= e(capistra_corporate_action_types()[$a['action_type']] ?? (string) $a['action_type']) ?></td>
          <td>
            <?php if ($a['amount'] !== null): ?>Amount <?= e(capistra_money((float) $a['amount'])) ?><?php endif; ?>
            <?php if ($a['ratio_from'] && $a['ratio_to']): ?>Ratio <?= e((string) $a['ratio_from']) ?>:<?= e((string) $a['ratio_to']) ?><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if ($actions === []): ?><tr><td class="empty-state" colspan="4">No corporate actions recorded.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h2>Apply action</h2>
    <form method="post" action="corporate-actions.php">
      <?= capistra_csrf_field() ?>
      <div class="form-row"><label for="security_id">Security</label>
        <select id="security_id" name="security_id" required><option value="">Select security…</option>
          <?php foreach ($securities as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['symbol'] . ' · ' . $s['company_name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="portfolio_id">Portfolio</label>
        <select id="portfolio_id" name="portfolio_id"><option value="0">—</option>
          <?php foreach ($portfolios as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e((string) $p['name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="action_type">Action type</label>
        <select id="action_type" name="action_type">
          <?php foreach (capistra_corporate_action_types() as $v => $l): ?><option value="<?= e($v) ?>"><?= e($l) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="action_date">Action date</label><input id="action_date" name="action_date" type="date" value="<?= e(date('Y-m-d')) ?>" required></div>
      <div class="form-row"><label for="amount">Cash amount</label><input id="amount" name="amount" type="number" step="0.01" value="0"></div>
      <div class="form-row"><label for="dividend_per_share">Dividend per share</label><input id="dividend_per_share" name="dividend_per_share" type="number" step="0.0001"></div>
      <div class="form-row"><label for="units">Units (bonus/rights/merger)</label><input id="units" name="units" type="number" step="0.0001" value="0"></div>
      <div class="form-row"><label for="price_per_unit">Price per unit (rights)</label><input id="price_per_unit" name="price_per_unit" type="number" step="0.0001" value="0"></div>
      <div class="form-row"><label for="ratio_from">Ratio from (split/consolidation)</label><input id="ratio_from" name="ratio_from" type="number" step="0.0001"></div>
      <div class="form-row"><label for="ratio_to">Ratio to</label><input id="ratio_to" name="ratio_to" type="number" step="0.0001"></div>
      <div class="form-row"><label for="notes">Notes</label><input id="notes" name="notes" maxlength="255"></div>
      <button class="btn btn--primary" type="submit">Apply action</button>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
