<?php
declare(strict_types=1);

/**
 * Capistra - shared accounting layout (sidebar + topbar + content shell).
 * Implements design-system/.../MASTER.md (Minimalism / Swiss, IBM Plex Sans).
 */

if (!function_exists('capistra_nav_items')) {
    function capistra_nav_items(): array
    {
        return [
            'Overview' => [
                'index.php'         => 'Finance Command Center',
                'planner.php'       => 'Scenario Planner',
            ],
            'Accounting' => [
                'chart-of-accounts.php' => 'Chart of Accounts',
                'journal.php'           => 'Journal Entries',
                'general-ledger.php'    => 'General Ledger',
                'trial-balance.php'     => 'Trial Balance',
                'profit-loss.php'       => 'Profit & Loss',
                'balance-sheet.php'     => 'Balance Sheet',
                'cash-flow.php'         => 'Cash Flow',
                'ledger-sync.php'       => 'Income/Expense Sync',
            ],
        ];
    }
}

if (!function_exists('capistra_layout_header')) {
    function capistra_layout_header(string $title, string $active, string $base = '.'): void
    {
        $user = capistra_current_user();
        ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> &middot; <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e($base) ?>/assets/css/capistra.css">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<div class="app-shell">
  <aside class="sidebar" id="sidebar" aria-label="Primary">
    <div class="sidebar__brand">
      <span class="mark" aria-hidden="true">C</span>
      <span><?= e(APP_NAME) ?></span>
    </div>
    <nav aria-label="Sections">
      <?php foreach (capistra_nav_items() as $group => $items): ?>
        <div class="sidebar__group">
          <div class="sidebar__group-title"><?= e($group) ?></div>
          <?php foreach ($items as $href => $label): ?>
            <a class="nav-link" href="<?= e($base . '/' . $href) ?>"
               <?= $href === $active ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
      <div class="sidebar__group">
        <div class="sidebar__group-title">Legacy Modules</div>
        <a class="nav-link" href="<?= e($base) ?>/../admin/index.php">Income &amp; Expenses</a>
        <a class="nav-link" href="<?= e($base) ?>/../admin/investment/index.php">Investments</a>
        <a class="nav-link" href="<?= e($base) ?>/../admin/fund/index.php">Investor &amp; Funds</a>
      </div>
    </nav>
  </aside>
  <div class="scrim" data-open="false" id="scrim" hidden></div>
  <div class="main">
    <header class="topbar">
      <button class="btn btn--secondary btn--sm topbar__toggle" id="sidebarToggle" aria-controls="sidebar" aria-expanded="false">Menu</button>
      <span class="topbar__title"><?= e($title) ?></span>
      <span class="topbar__spacer"></span>
      <span class="badge"><?= e(APP_BASE_CURRENCY) ?></span>
      <?php if ($user): ?><span class="badge"><?= e($user['full_name']) ?></span><?php endif; ?>
      <a class="btn btn--secondary btn--sm" href="<?= e($base) ?>/../admin/logout.php">Sign out</a>
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
