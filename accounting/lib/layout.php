<?php
declare(strict_types=1);

/**
 * Capistra - shared layout (sidebar + topbar + content shell).
 * Implements design-system/.../MASTER.md (Minimalism / Swiss, IBM Plex Sans).
 *
 * Navigation is FEATURE-AWARE: modules disabled in Settings -> Modules are
 * hidden here, and the pages themselves also guard direct URL access via
 * `capistra_guard_module()`.
 */

require_once dirname(__DIR__, 2) . '/config/features.php';

if (!function_exists('capistra_url')) {
    /** Build a root-relative application URL that respects the install path. */
    function capistra_url(string $path = ''): string
    {
        $base = defined('APP_URL') ? rtrim((string) APP_URL, '/') : '';
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('capistra_nav_items')) {
    /**
     * @return array<string, array<string,string>> group => [href => label]
     */
    function capistra_nav_items(): array
    {
        $nav = [
            'Overview' => [
                'accounting/index.php' => 'Finance Command Center',
                'accounting/planner.php' => 'Scenario Planner',
            ],
            'Accounting' => [
                'accounting/chart-of-accounts.php' => 'Chart of Accounts',
                'accounting/journal.php' => 'Journal Entries',
                'accounting/general-ledger.php' => 'General Ledger',
                'accounting/trial-balance.php' => 'Trial Balance',
                'accounting/profit-loss.php' => 'Profit & Loss',
                'accounting/balance-sheet.php' => 'Balance Sheet',
                'accounting/cash-flow.php' => 'Cash Flow',
                'accounting/ledger-sync.php' => 'Income/Expense Sync',
            ],
        ];

        // Personal finance (only enabled modules).
        $personal = [];
        if (capistra_feature_enabled('net_worth'))     { $personal['admin/finance/index.php'] = 'Net Worth & Overview'; }
        if (capistra_feature_enabled('budgeting'))     { $personal['admin/finance/budgets.php'] = 'Budgets'; }
        if (capistra_feature_enabled('recurring'))     { $personal['admin/finance/recurring.php'] = 'Recurring Transactions'; }
        if (capistra_feature_enabled('goals'))         { $personal['admin/finance/goals.php'] = 'Goals'; }
        $personal['admin/finance/transfers.php'] = 'Transfers';
        if (capistra_feature_enabled('reconciliation')){ $personal['admin/finance/reconcile.php'] = 'Reconciliation'; }
        if (capistra_feature_enabled('tags'))          { $personal['admin/finance/tags.php'] = 'Tags'; }
        if ($personal !== []) { $nav['Personal Finance'] = $personal; }

        // Investments.
        $invest = [];
        if (capistra_feature_enabled('stocks')) {
            $invest['admin/investment/portfolio.php'] = 'Stock Portfolio';
            $invest['admin/investment/stock-transactions.php'] = 'Stock Transactions';
            $invest['admin/investment/corporate-actions.php'] = 'Corporate Actions';
        }
        if (capistra_feature_enabled('market_data')) { $invest['admin/investment/prices.php'] = 'Market Prices'; }
        if ($invest !== []) { $nav['Investments'] = $invest; }

        // Data.
        if (capistra_feature_enabled('data_import')) {
            $nav['Data'] = ['admin/settings/data.php' => 'Import / Export'];
        }

        // Settings.
        $nav['Settings'] = ['admin/settings/index.php' => 'Settings Center'];

        // Legacy modules (feature-gated where applicable).
        $legacy = ['admin/index.php' => 'Income & Expenses'];
        $legacy['admin/investment/index.php'] = 'Investments (Legacy)';
        if (capistra_feature_enabled('investor_management')) { $legacy['admin/fund/fundmanagement.php'] = 'Investor & Funds'; }
        $nav['Legacy Modules'] = $legacy;

        return $nav;
    }
}

if (!function_exists('capistra_layout_header')) {
    function capistra_layout_header(string $title, string $active, string $base = '..'): void
    {
        $user = capistra_current_user();
        ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> &middot; <?= e((string) capistra_setting('display_name', APP_NAME)) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(capistra_url('assets/css/capistra.css')) ?>">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<div class="app-shell">
  <aside class="sidebar" id="sidebar" aria-label="Primary">
    <div class="sidebar__brand">
      <span class="mark" aria-hidden="true">C</span>
      <span><?= e((string) capistra_setting('display_name', APP_NAME)) ?></span>
    </div>
    <nav aria-label="Sections">
      <?php foreach (capistra_nav_items() as $group => $items): ?>
        <div class="sidebar__group">
          <div class="sidebar__group-title"><?= e($group) ?></div>
          <?php foreach ($items as $href => $label): ?>
            <?php $isActive = ($active === $href) || (basename($href) === $active); ?>
            <a class="nav-link" href="<?= e(capistra_url($href)) ?>"
               <?= $isActive ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </nav>
  </aside>
  <div class="scrim" data-open="false" id="scrim" hidden></div>
  <div class="main">
    <header class="topbar">
      <button class="btn btn--secondary btn--sm topbar__toggle" id="sidebarToggle" aria-controls="sidebar" aria-expanded="false">Menu</button>
      <span class="topbar__title"><?= e($title) ?></span>
      <span class="topbar__spacer"></span>
      <span class="badge"><?= e(capistra_base_currency()) ?></span>
      <?php if ($user): ?><span class="badge"><?= e($user['full_name']) ?></span><?php endif; ?>
      <a class="btn btn--secondary btn--sm" href="<?= e(capistra_url('admin/logout.php')) ?>">Sign out</a>
    </header>
    <main class="content" id="main-content">
<?php
    }
}

if (!function_exists('capistra_layout_footer')) {
    function capistra_layout_footer(): void
    {
        ?>
    </main>
  </div>
</div>
<script>
(function () {
  var toggle = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('sidebar');
  var scrim = document.getElementById('scrim');
  function setOpen(open) {
    sidebar.setAttribute('data-open', String(open));
    if (scrim) { scrim.setAttribute('data-open', String(open)); scrim.hidden = !open; }
    if (toggle) { toggle.setAttribute('aria-expanded', String(open)); }
  }
  if (toggle) {
    toggle.addEventListener('click', function () {
      setOpen(sidebar.getAttribute('data-open') !== 'true');
    });
  }
  if (scrim) { scrim.addEventListener('click', function () { setOpen(false); }); }
})();
</script>
</body>
</html><?php
    }
}

if (!function_exists('capistra_amount')) {
    /** Render a signed amount with a non-colour cue (label) for accessibility. */
    function capistra_amount(float $value, bool $colour = true): string
    {
        $formatted = capistra_currency(abs($value));
        if ($value < 0) {
            return '<span class="amount ' . ($colour ? 'amount--neg' : '') . '">(' . e($formatted) . ')</span>';
        }
        return '<span class="amount ' . ($colour ? 'amount--pos' : '') . '">' . e($formatted) . '</span>';
    }
}

if (!function_exists('capistra_section_tabs')) {
    /**
     * Render a tab bar for the Settings Center and similar multi-page hubs.
     *
     * @param array<string,string> $tabs  href => label
     */
    function capistra_section_tabs(array $tabs, string $active): void
    {
        echo '<div class="tabs" role="tablist">';
        foreach ($tabs as $href => $label) {
            $isActive = basename($href) === basename($active) || $href === $active;
            echo '<a class="tab" role="tab" href="' . e($href) . '"' . ($isActive ? ' aria-current="page"' : '') . '>' . e($label) . '</a>';
        }
        echo '</div>';
    }
}
