<?php
declare(strict_types=1);

/**
 * Capistra - shared bootstrap for the admin settings / finance / investment
 * screens built in this phase. Provides authentication, the shared layout,
 * flash messages and the domain libraries. No queries run at include time.
 */

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/session.php';
require_once dirname(__DIR__, 2) . '/config/settings.php';
require_once dirname(__DIR__, 2) . '/config/features.php';
require_once dirname(__DIR__, 2) . '/accounting/lib/auth.php';
require_once dirname(__DIR__, 2) . '/accounting/lib/layout.php';
require_once dirname(__DIR__, 2) . '/lib/categories.php';
require_once dirname(__DIR__, 2) . '/lib/financial_accounts.php';
require_once dirname(__DIR__, 2) . '/lib/transfers.php';
require_once dirname(__DIR__, 2) . '/lib/nepse.php';
require_once dirname(__DIR__, 2) . '/lib/portfolio.php';
require_once dirname(__DIR__, 2) . '/lib/personal_finance.php';
require_once dirname(__DIR__, 2) . '/lib/periods.php';
require_once dirname(__DIR__, 2) . '/lib/csv.php';

if (!function_exists('capistra_admin_guard')) {
    /** Authenticate the request for the admin hub screens. */
    function capistra_admin_guard(): array
    {
        capistra_session_start();

        if (!capistra_enforce_timeout()) {
            capistra_session_destroy();
            header('Location: ' . capistra_url('index.php?timeout=1'));
            exit;
        }

        $user = capistra_current_user();
        if ($user === null) {
            header('Location: ' . capistra_url('index.php?error=session_expired'));
            exit;
        }
        return $user;
    }
}

if (!function_exists('capistra_flash')) {
    function capistra_flash_set(string $type, string $message): void
    {
        capistra_session_start();
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('capistra_flash_render')) {
    function capistra_flash_render(): void
    {
        capistra_session_start();
        $items = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        foreach ($items as $item) {
            $class = $item['type'] === 'error' ? 'alert--error' : ($item['type'] === 'success' ? 'alert--success' : 'alert--info');
            echo '<div class="alert ' . $class . '">' . e((string) $item['message']) . '</div>';
        }
    }
}

if (!function_exists('capistra_settings_tabs')) {
    /** @return array<string,string> href => label */
    function capistra_settings_tabs(): array
    {
        $tabs = [
            'index.php'          => 'General',
            'features.php'       => 'Modules',
            'categories.php'     => 'Categories',
            'accounts.php'       => 'Accounts & Wallets',
            'accounting.php'     => 'Accounting',
            'fiscal.php'         => 'Fiscal Periods',
            'nepse.php'          => 'Securities',
            'sectors.php'        => 'Sectors',
            'fees.php'           => 'Fees & Taxes',
            'classifications.php'=> 'Classifications',
            'data.php'           => 'Import / Export',
            'backup.php'         => 'Backup',
        ];
        return $tabs;
    }
}

if (!function_exists('capistra_render_settings_nav')) {
    function capistra_render_settings_nav(string $active): void
    {
        echo '<nav class="tabs" aria-label="Settings sections">';
        foreach (capistra_settings_tabs() as $file => $label) {
            $isActive = basename($file) === basename($active);
            echo '<a class="tab" href="' . e($file) . '"' . ($isActive ? ' aria-current="page"' : '') . '>' . e($label) . '</a>';
        }
        echo '</nav>';
    }
}

if (!function_exists('capistra_post_string')) {
    /** Read a trimmed POST string. */
    function capistra_post_string(string $key, string $default = ''): string
    {
        return trim((string) ($_POST[$key] ?? $default));
    }
}
