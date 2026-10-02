<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
capistra_guard_module('market_data');
$pdo = capistra_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            if (capistra_post_string('action') === 'import') {
                if (empty($_FILES['csv']['tmp_name']) || !is_uploaded_file($_FILES['csv']['tmp_name'])) {
                    throw new RuntimeException('Please choose a CSV file.');
                }
                $parsed = capistra_csv_parse((string) file_get_contents($_FILES['csv']['tmp_name']));
                $ok = 0; $errors = [];
                foreach ($parsed['rows'] as $i => $row) {
                    $line = $i + 2;
                    $symbol = strtoupper(trim((string) ($row['symbol'] ?? '')));
                    $sec = $symbol !== '' ? capistra_security_by_symbol($symbol) : null;
                    if (!$sec) { $errors[] = "Line $line: unknown symbol " . ($symbol ?: '(empty)'); continue; }
                    $date = trim((string) ($row['price_date'] ?? $row['date'] ?? ''));
                    $close = (float) ($row['close'] ?? $row['ltp'] ?? 0);
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $close <= 0) { $errors[] = "Line $line: invalid date or close price."; continue; }
                    capistra_record_price([
                        'security_id' => (int) $sec['id'], 'price_date' => $date, 'close' => $close,
                        'open' => isset($row['open']) ? (float) $row['open'] : null,
                        'high' => isset($row['high']) ? (float) $row['high'] : null,
                        'low'  => isset($row['low']) ? (float) $row['low'] : null,
                        'volume' => isset($row['volume']) && $row['volume'] !== '' ? (int) $row['volume'] : null,
                        'source' => 'csv',
                    ]);
                    $ok++;
                }
                capistra_log_import('security_prices', 'prices.csv', count($parsed['rows']), $ok, count($errors), 'committed', implode("\n", $errors));
                capistra_audit($pdo, 'import', 'security_price', null, "Imported $ok price points");
                capistra_flash_set($ok ? 'success' : 'error', "Imported $ok price points." . ($errors ? ' ' . count($errors) . ' rows skipped.' : ''));
            } else {
                $secId = (int) ($_POST['security_id'] ?? 0);
                $date = capistra_post_string('price_date', date('Y-m-d'));
                $close = (float) capistra_post_string('close', '0');
                if ($secId <= 0 || $close <= 0) { throw new RuntimeException('Security and a positive close price are required.'); }
                capistra_record_price([
                    'security_id' => $secId, 'price_date' => $date, 'close' => $close,
                    'open' => capistra_post_string('open') !== '' ? (float) $_POST['open'] : null,
                    'high' => capistra_post_string('high') !== '' ? (float) $_POST['high'] : null,
                    'low'  => capistra_post_string('low') !== '' ? (float) $_POST['low'] : null,
                    'volume' => capistra_post_string('volume') !== '' ? (int) $_POST['volume'] : null,
                    'source' => 'manual',
                ]);
                capistra_audit($pdo, 'create', 'security_price', $secId, $date);
                capistra_flash_set('success', 'Price recorded (history preserved).');
            }
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not record price: ' . $e->getMessage());
        }
    }
    header('Location: prices.php');
    exit;
}

$securityId = (int) ($_GET['security_id'] ?? 0);
$securities = capistra_securities(['active_only' => true]);
$history = $securityId > 0 ? capistra_price_history($securityId) : [];
$latest = [];
foreach (array_slice($securities, 0, 50) as $s) {
    $latest[(int) $s['id']] = capistra_latest_price((int) $s['id']);
}

capistra_layout_header('Market Prices', 'prices.php', '../../');
?>
<h1>Market Prices</h1>
<p class="hint">Historical prices are append-only and never truncated. Manual and CSV entry always work even when no live provider is available.</p>
<?php capistra_flash_render(); ?>

<div class="grid grid--2">
  <div>
    <div class="card">
      <h2>Record a price</h2>
      <form method="post" action="prices.php">
        <?= capistra_csrf_field() ?>
        <input type="hidden" name="action" value="manual">
        <div class="form-row"><label for="security_id">Security</label>
          <select id="security_id" name="security_id" required><option value="">Select security…</option>
            <?php foreach ($securities as $s): ?><option value="<?= (int) $s['id'] ?>" <?= $securityId === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['symbol'] . ' · ' . $s['company_name']) ?></option><?php endforeach; ?>
          </select></div>
        <div class="form-row"><label for="price_date">Date</label><input id="price_date" name="price_date" type="date" value="<?= e(date('Y-m-d')) ?>" required></div>
        <div class="form-row"><label for="close">Close / LTP</label><input id="close" name="close" type="number" step="0.01" required></div>
        <div class="form-row"><label for="open">Open</label><input id="open" name="open" type="number" step="0.01"></div>
        <div class="form-row"><label for="high">High</label><input id="high" name="high" type="number" step="0.01"></div>
        <div class="form-row"><label for="low">Low</label><input id="low" name="low" type="number" step="0.01"></div>
        <div class="form-row"><label for="volume">Volume</label><input id="volume" name="volume" type="number"></div>
        <button class="btn btn--primary" type="submit">Record price</button>
      </form>
    </div>
    <div class="card" style="margin-top:var(--space-2xl)">
      <h2>CSV import</h2>
      <p class="hint">Columns: <code>symbol,price_date,close</code> (optional open/high/low/volume).</p>
      <form method="post" enctype="multipart/form-data" action="prices.php">
        <?= capistra_csrf_field() ?>
        <input type="hidden" name="action" value="import">
        <div class="form-row"><label for="csv">CSV file</label><input id="csv" type="file" name="csv" accept=".csv,text/csv" required></div>
        <button class="btn btn--secondary" type="submit">Import prices</button>
      </form>
    </div>
  </div>

  <div>
    <div class="card">
      <h2>Latest prices</h2>
      <div class="table-wrap" style="max-height:420px;overflow:auto">
        <table class="data">
          <thead><tr><th scope="col">Symbol</th><th scope="col">Date</th><th scope="col" class="num">Close</th><th scope="col">Source</th></tr></thead>
          <tbody>
          <?php foreach ($securities as $s): $row = $latest[(int) $s['id']] ?? null; ?>
            <tr><th scope="row"><a href="prices.php?security_id=<?= (int) $s['id'] ?>"><?= e((string) $s['symbol']) ?></a></th>
              <td><?= $row ? e((string) $row['price_date']) : '—' ?></td>
              <td class="num"><?= $row ? e(capistra_money((float) $row['close'])) : '—' ?></td>
              <td><?= $row ? e((string) $row['source']) : '—' ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($securityId > 0): ?>
      <div class="card" style="margin-top:var(--space-2xl)">
        <h2>Price history</h2>
        <div class="table-wrap" style="max-height:320px;overflow:auto">
          <table class="data">
            <thead><tr><th scope="col">Date</th><th scope="col" class="num">Close</th><th scope="col" class="num">Volume</th><th scope="col">Source</th></tr></thead>
            <tbody>
            <?php foreach ($history as $h): ?>
              <tr><th scope="row"><?= e((string) $h['price_date']) ?></th><td class="num"><?= e(capistra_money((float) $h['close'])) ?></td><td class="num"><?= $h['volume'] !== null ? e((string) $h['volume']) : '—' ?></td><td><?= e((string) $h['source']) ?></td></tr>
            <?php endforeach; ?>
            <?php if ($history === []): ?><tr><td class="empty-state" colspan="4">No history for this security.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php capistra_layout_footer(); ?>
