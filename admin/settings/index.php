<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
$pdo  = capistra_pdo();

$currencies = ['NPR', 'USD', 'EUR', 'GBP', 'INR', 'AUD', 'CAD', 'JPY', 'CNY'];
$fields = [
    'display_name', 'company_name', 'company_address', 'company_tax_id',
    'financial_profile_mode', 'base_currency', 'currency_symbol_style', 'number_grouping',
    'decimal_precision', 'timezone', 'date_format', 'date_display_mode',
    'fiscal_year_start', 'default_reporting_period', 'dashboard_landing',
    'low_cash_threshold', 'portfolio_concentration_limit',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            foreach ($fields as $field) {
                if (array_key_exists($field, $_POST)) {
                    capistra_set_setting($pdo, $field, trim((string) $_POST[$field]));
                }
            }
            capistra_audit($pdo, 'update', 'settings', null, 'Updated general settings');
            capistra_flash_set('success', 'Settings saved.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save settings: ' . $e->getMessage());
        }
    }
    header('Location: index.php');
    exit;
}

capistra_layout_header('Settings - General', 'index.php', '../../');
?>
<h1>Settings Center</h1>
<p class="hint">Scalar preferences are stored in <code>app_settings</code>. Financial master data lives in dedicated tables (Categories, Accounts, Securities, ...).</p>
<?php capistra_render_settings_nav('index.php'); capistra_flash_render(); ?>

<div class="grid grid--2">
  <div class="card">
    <h2>Organisation &amp; identity</h2>
    <form method="post" action="index.php">
      <?= capistra_csrf_field() ?>
      <div class="form-row"><label for="display_name">Display name</label>
        <input id="display_name" name="display_name" value="<?= e((string) capistra_setting('display_name', APP_NAME)) ?>" maxlength="120"></div>
      <div class="form-row"><label for="company_name">Legal / company name</label>
        <input id="company_name" name="company_name" value="<?= e((string) capistra_setting('company_name')) ?>" maxlength="150"></div>
      <div class="form-row"><label for="company_address">Address</label>
        <input id="company_address" name="company_address" value="<?= e((string) capistra_setting('company_address')) ?>" maxlength="255"></div>
      <div class="form-row"><label for="company_tax_id">Tax / PAN identifier</label>
        <input id="company_tax_id" name="company_tax_id" value="<?= e((string) capistra_setting('company_tax_id')) ?>" maxlength="60"></div>
      <div class="form-row"><label for="financial_profile_mode">Financial profile mode</label>
        <select id="financial_profile_mode" name="financial_profile_mode">
          <?php foreach (['personal' => 'Personal', 'business' => 'Business', 'hybrid' => 'Hybrid'] as $v => $l): ?>
            <option value="<?= e($v) ?>" <?= capistra_profile_mode() === $v ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="hint">Controls sensible defaults for which modules are enabled. Individual toggles are on the Modules tab.</p></div>
      <button class="btn btn--primary" type="submit">Save</button>
    </form>
  </div>

  <div class="card">
    <h2>Money, locale &amp; reporting</h2>
    <form method="post" action="index.php">
      <?= capistra_csrf_field() ?>
      <div class="form-row"><label for="base_currency">Base currency</label>
        <select id="base_currency" name="base_currency">
          <?php foreach ($currencies as $c): ?>
            <option value="<?= e($c) ?>" <?= capistra_base_currency() === $c ? 'selected' : '' ?>><?= e($c) ?> (<?= e(capistra_currency_symbol($c)) ?>)</option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="currency_symbol_style">Symbol position</label>
        <select id="currency_symbol_style" name="currency_symbol_style">
          <option value="prefix" <?= capistra_setting('currency_symbol_style') === 'prefix' ? 'selected' : '' ?>>Prefix (Rs. 1,000)</option>
          <option value="suffix" <?= capistra_setting('currency_symbol_style') === 'suffix' ? 'selected' : '' ?>>Suffix (1,000 Rs.)</option>
        </select></div>
      <div class="form-row"><label for="number_grouping">Number grouping</label>
        <select id="number_grouping" name="number_grouping">
          <option value="international" <?= capistra_setting('number_grouping') === 'international' ? 'selected' : '' ?>>International (1,234,567)</option>
          <option value="south_asian" <?= capistra_setting('number_grouping') === 'south_asian' ? 'selected' : '' ?>>South Asian (12,34,567)</option>
        </select></div>
      <div class="form-row"><label for="decimal_precision">Decimal precision (0-4)</label>
        <input id="decimal_precision" name="decimal_precision" type="number" min="0" max="4" value="<?= e((string) capistra_setting('decimal_precision', '2')) ?>"></div>
      <div class="form-row"><label for="timezone">Timezone</label>
        <input id="timezone" name="timezone" value="<?= e((string) capistra_setting('timezone')) ?>" placeholder="Asia/Kathmandu"></div>
      <div class="form-row"><label for="date_format">Date format</label>
        <input id="date_format" name="date_format" value="<?= e((string) capistra_setting('date_format')) ?>" placeholder="Y-m-d"></div>
      <div class="form-row"><label for="date_display_mode">Date display</label>
        <select id="date_display_mode" name="date_display_mode">
          <?php foreach (['AD' => 'AD (Gregorian)', 'BS' => 'BS', 'both' => 'Both (requires BS support)'] as $v => $l): ?>
            <option value="<?= e($v) ?>" <?= capistra_setting('date_display_mode') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="fiscal_year_start">Fiscal year start (MM-DD)</label>
        <input id="fiscal_year_start" name="fiscal_year_start" value="<?= e(capistra_fiscal_year_start()) ?>" pattern="\d{2}-\d{2}"></div>
      <div class="form-row"><label for="default_reporting_period">Default reporting period</label>
        <select id="default_reporting_period" name="default_reporting_period">
          <?php foreach (['fiscal_year' => 'Fiscal year', 'calendar_year' => 'Calendar year', 'month' => 'Month', 'quarter' => 'Quarter'] as $v => $l): ?>
            <option value="<?= e($v) ?>" <?= capistra_setting('default_reporting_period') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="form-row"><label for="low_cash_threshold">Low cash-reserve threshold</label>
        <input id="low_cash_threshold" name="low_cash_threshold" type="number" step="0.01" value="<?= e((string) capistra_setting('low_cash_threshold', '0')) ?>"></div>
      <div class="form-row"><label for="portfolio_concentration_limit">Portfolio concentration limit (%)</label>
        <input id="portfolio_concentration_limit" name="portfolio_concentration_limit" type="number" min="1" max="100" value="<?= e((string) capistra_setting('portfolio_concentration_limit', '35')) ?>"></div>
      <input type="hidden" name="dashboard_landing" value="<?= e((string) capistra_setting('dashboard_landing', 'accounting/index.php')) ?>">
      <button class="btn btn--primary" type="submit">Save</button>
    </form>
  </div>
</div>

<div class="card" style="margin-top:var(--space-2xl)">
  <h2>Preview</h2>
  <table class="data">
    <tbody>
      <tr><th scope="row">Currency</th><td><?= e(capistra_money(1234567.89)) ?></td></tr>
      <tr><th scope="row">Fiscal year (current)</th><td><?= e(implode(' → ', capistra_fiscal_year_bounds())) ?></td></tr>
      <tr><th scope="row">Profile mode</th><td><?= e(capistra_profile_mode()) ?></td></tr>
    </tbody>
  </table>
</div>
<?php capistra_layout_footer(); ?>
