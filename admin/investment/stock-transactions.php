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
            $id = capistra_add_stock_transaction([
                'portfolio_id'        => (int) ($_POST['portfolio_id'] ?? 0),
                'security_id'         => (int) ($_POST['security_id'] ?? 0),
                'transaction_type'    => capistra_post_string('transaction_type', 'BUY'),
                'transaction_date'    => capistra_post_string('transaction_date', date('Y-m-d')),
                'units'               => capistra_post_string('units', '0'),
                'price_per_unit'      => capistra_post_string('price_per_unit', '0'),
                'fees'                => capistra_post_string('fees', '0'),
                'tax'                 => capistra_post_string('tax', '0'),
                'financial_account_id'=> (int) ($_POST['financial_account_id'] ?? 0),
                'reference'           => capistra_post_string('reference'),
                'notes'               => capistra_post_string('notes'),
            ]);
            capistra_audit($pdo, 'create', 'stock_transaction', $id, capistra_post_string('transaction_type'));
            capistra_flash_set('success', 'Transaction recorded.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not record transaction: ' . $e->getMessage());
        }
    }
    header('Location: stock-transactions.php');
    exit;
}

$transactions = capistra_stock_transactions();
$securities = capistra_securities(['active_only' => true]);
$portfolios = capistra_portfolios(true);
$accounts = capistra_financial_accounts(true);

capistra_layout_header('Stock Transactions', 'stock-transactions.php', '../../');
?>
<h1>Stock Transactions</h1>
<p class="hint">Append-only ledger of buys, sells, allotments, bonuses, dividends and adjustments. Positions are computed from these rows.</p>
<?php capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="table-wrap" style="max-height:620px;overflow:auto">
    <table class="data">
      <thead><tr><th scope="col">Date</th><th scope="col">Symbol</th><th scope="col">Type</th><th scope="col" class="num">Units</th><th scope="col" class="num">Price</th><th scope="col" class="num">Fees</th><th scope="col" class="num">Net</th></tr></thead>
      <tbody>
      <?php foreach ($transactions as $t): ?>
        <tr>
          <th scope="row"><?= e((string) $t['transaction_date']) ?></th>
          <td><?= e((string) $t['symbol']) ?></td>
          <td><?= e(capistra_stock_transaction_types()[$t['transaction_type']] ?? (string) $t['transaction_type']) ?></td>
          <td class="num"><?= e(number_format((float) $t['units'], 2)) ?></td>
          <td class="num"><?= (float) $t['price_per_unit'] > 0 ? e(capistra_money((float) $t['price_per_unit'])) : '—' ?></td>
          <td class="num"><?= e(capistra_money((float) $t['fees'])) ?></td>
          <td class="num"><?= capistra_amount((float) $t['net_amount']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($transactions === []): ?><tr><td class="empty-state" colspan="7">No transactions yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>Record transaction</h2>
    <form method="post" action="stock-transactions.php">
      <?= capistra_csrf_field() ?>
      <div class="form-row"><label for="security_id">Security</label>
        <select id="security_id" name="security_id" required>
          <option value="">Select security…</option>
          <?php foreach ($securities as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['symbol'] . ' · ' . $s['company_name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="transaction_type">Type</label>
        <select id="transaction_type" name="transaction_type">
          <?php foreach (capistra_stock_transaction_types() as $v => $l): ?><option value="<?= e($v) ?>"><?= e($l) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="portfolio_id">Portfolio</label>
        <select id="portfolio_id" name="portfolio_id"><option value="0">—</option>
          <?php foreach ($portfolios as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e((string) $p['name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="transaction_date">Date</label><input id="transaction_date" name="transaction_date" type="date" value="<?= e(date('Y-m-d')) ?>" required></div>
      <div class="form-row"><label for="units">Units</label><input id="units" name="units" type="number" step="0.0001" required></div>
      <div class="form-row"><label for="price_per_unit">Price per unit</label><input id="price_per_unit" name="price_per_unit" type="number" step="0.0001" value="0"></div>
      <div class="form-row"><label for="fees">Fees</label><input id="fees" name="fees" type="number" step="0.01" value="0"></div>
      <div class="form-row"><label for="tax">Tax</label><input id="tax" name="tax" type="number" step="0.01" value="0"></div>
      <div class="form-row"><label for="financial_account_id">Funded from</label>
        <select id="financial_account_id" name="financial_account_id"><option value="0">—</option>
          <?php foreach ($accounts as $a): ?><option value="<?= (int) $a['id'] ?>"><?= e((string) $a['name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="reference">Reference</label><input id="reference" name="reference" maxlength="60"></div>
      <button class="btn btn--primary" type="submit">Record</button>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
