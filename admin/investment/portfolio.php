<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
capistra_guard_module('stocks');
$pdo = capistra_pdo();

$asOf = (string) ($_GET['as_of'] ?? '');
$summary = capistra_portfolio_summary($asOf !== '' ? $asOf : null);
$concentrationLimit = (float) capistra_setting('portfolio_concentration_limit', '35');

$positions = array_values(array_filter($summary['positions'], static fn($p) => (float) $p['units'] > 0.0001));
$gainers = $positions; usort($gainers, static fn($a, $b) => $b['unrealized'] <=> $a['unrealized']);
$topGainers = array_slice($gainers, 0, 5);
$losers = array_reverse($gainers); $topLosers = array_slice($losers, -5);
$totalMarket = max((float) $summary['market_value'], 0.01);

capistra_layout_header('Stock Portfolio', 'portfolio.php', '../../');
?>
<h1>Stock Portfolio</h1>
<p class="hint">Every figure is derived from the stock transaction ledger — nothing is stored as a static position. <a href="<?= e(capistra_url('admin/investment/stock-transactions.php')) ?>">Manage transactions</a>.</p>

<div class="grid grid--kpi">
  <div class="card"><div class="card__label">Market value</div><div class="card__value"><?= e(capistra_money($summary['market_value'])) ?></div></div>
  <div class="card"><div class="card__label">Cost basis (held)</div><div class="card__value"><?= e(capistra_money($summary['total_cost'])) ?></div></div>
  <div class="card"><div class="card__label">Unrealized P&amp;L</div><div class="card__value"><?= capistra_amount((float) $summary['unrealized']) ?></div></div>
  <div class="card"><div class="card__label">Realized P&amp;L</div><div class="card__value"><?= capistra_amount((float) $summary['realized']) ?></div></div>
  <div class="card"><div class="card__label">Dividends</div><div class="card__value"><?= e(capistra_money($summary['dividends'])) ?></div></div>
  <div class="card"><div class="card__label">Total return</div><div class="card__value"><?= capistra_amount((float) $summary['total_return']) ?></div></div>
</div>

<div class="grid grid--2" style="margin-top:var(--space-3xl)">
  <div class="card">
    <h2>Allocation by sector</h2>
    <table class="data">
      <thead><tr><th scope="col">Sector</th><th scope="col" class="num">Value</th><th scope="col" class="num">Weight</th></tr></thead>
      <tbody>
      <?php foreach ($summary['allocation_sector'] as $sector => $value): ?>
        <?php $weight = round(((float) $value / $totalMarket) * 100, 1); ?>
        <tr>
          <th scope="row"><?= e((string) $sector) ?><?php if ($weight > $concentrationLimit): ?> <span class="badge badge--neg">Concentrated</span><?php endif; ?></th>
          <td class="num"><?= e(capistra_money((float) $value)) ?></td>
          <td class="num"><?= e((string) $weight) ?>%</td>
        </tr>
      <?php endforeach; ?>
      <?php if ($summary['allocation_sector'] === []): ?><tr><td class="empty-state" colspan="3">No holdings.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h2>Top gainers / losers</h2>
    <table class="data">
      <thead><tr><th scope="col">Security</th><th scope="col" class="num">Unrealized</th></tr></thead>
      <tbody>
      <?php foreach (array_slice($topGainers, 0, 3) as $p): ?>
        <tr><th scope="row"><?= e((string) $p['symbol']) ?> <span class="badge badge--pos">▲</span></th><td class="num"><?= capistra_amount((float) $p['unrealized']) ?></td></tr>
      <?php endforeach; ?>
      <?php foreach (array_slice(array_reverse($topLosers), 0, 3) as $p): ?>
        <?php if ((float) $p['unrealized'] < 0): ?><tr><th scope="row"><?= e((string) $p['symbol']) ?> <span class="badge badge--neg">▼</span></th><td class="num"><?= capistra_amount((float) $p['unrealized']) ?></td></tr><?php endif; ?>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<h2 style="margin-top:var(--space-3xl)">Holdings</h2>
<div class="table-wrap">
  <table class="data">
    <thead><tr><th scope="col">Symbol</th><th scope="col">Company</th><th scope="col">Sector</th><th scope="col" class="num">Units</th><th scope="col" class="num">Avg cost</th><th scope="col" class="num">Latest</th><th scope="col" class="num">Market value</th><th scope="col" class="num">Unrealized</th><th scope="col" class="num">Realized</th><th scope="col" class="num">Dividends</th></tr></thead>
    <tbody>
    <?php foreach ($positions as $p): ?>
      <tr>
        <th scope="row"><?= e((string) $p['symbol']) ?></th>
        <td><?= e((string) $p['company_name']) ?></td>
        <td><?= e((string) $p['sector_name']) ?></td>
        <td class="num"><?= e(number_format((float) $p['units'], 2)) ?></td>
        <td class="num"><?= e(capistra_money((float) $p['average_cost'])) ?></td>
        <td class="num"><?= $p['latest_price'] === null ? '—' : e(capistra_money((float) $p['latest_price'])) ?></td>
        <td class="num"><?= e(capistra_money((float) $p['market_value'])) ?></td>
        <td class="num"><?= capistra_amount((float) $p['unrealized']) ?></td>
        <td class="num"><?= capistra_amount((float) $p['realized_gain']) ?></td>
        <td class="num"><?= e(capistra_money((float) $p['dividends'])) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if ($positions === []): ?><tr><td class="empty-state" colspan="10">No stock transactions recorded yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php capistra_layout_footer(); ?>
