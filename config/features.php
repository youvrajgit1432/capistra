<?php
declare(strict_types=1);

/**
 * Capistra - feature module registry & toggles.
 *
 * Capistra is personal-finance-first but reusable for business/hybrid use.
 * `financial_profile_mode` chooses sensible defaults; each module can then be
 * toggled independently from Settings -> Modules.
 *
 * Toggles are ENFORCED, not cosmetic:
 *   - navigation hides disabled modules, and
 *   - `capistra_guard_module()` blocks direct URL access to them.
 */

require_once __DIR__ . '/settings.php';

if (!function_exists('capistra_feature_registry')) {
    /**
     * @return array<string,array{label:string,group:string,description:string,business:bool,personal:bool}>
     */
    function capistra_feature_registry(): array
    {
        return [
            // --- Personal finance -------------------------------------------------
            'budgeting' => ['label' => 'Budgets', 'group' => 'Personal Finance', 'description' => 'Monthly, category-level spending budgets.', 'personal' => true, 'business' => true],
            'goals' => ['label' => 'Goals', 'group' => 'Personal Finance', 'description' => 'Savings goals with targets and progress.', 'personal' => true, 'business' => false],
            'recurring' => ['label' => 'Recurring Transactions', 'group' => 'Personal Finance', 'description' => 'Salary, rent, bills and SIP rules.', 'personal' => true, 'business' => true],
            'tags' => ['label' => 'Tags', 'group' => 'Personal Finance', 'description' => 'Cross-cutting transaction labels.', 'personal' => true, 'business' => true],
            'reconciliation' => ['label' => 'Reconciliation', 'group' => 'Personal Finance', 'description' => 'Compare statements with system balances.', 'personal' => true, 'business' => true],
            'data_import' => ['label' => 'CSV Import / Export', 'group' => 'Personal Finance', 'description' => 'Safe CSV import and export of core data.', 'personal' => true, 'business' => true],
            'net_worth' => ['label' => 'Net Worth', 'group' => 'Personal Finance', 'description' => 'Assets minus liabilities overview.', 'personal' => true, 'business' => false],

            // --- Investments ------------------------------------------------------
            'nepse' => ['label' => 'NEPSE Securities', 'group' => 'Investments', 'description' => 'Listed security & sector master data.', 'personal' => true, 'business' => true],
            'market_data' => ['label' => 'Market Prices', 'group' => 'Investments', 'description' => 'Historical prices & manual/CSV entry.', 'personal' => true, 'business' => true],
            'stocks' => ['label' => 'Stock Portfolio', 'group' => 'Investments', 'description' => 'Transaction-based stock holdings.', 'personal' => true, 'business' => true],
            'business_investments' => ['label' => 'Business Investments', 'group' => 'Investments', 'description' => 'Equity / debt in private businesses.', 'personal' => false, 'business' => true],
            'real_estate' => ['label' => 'Real Estate', 'group' => 'Investments', 'description' => 'Property investments and income.', 'personal' => true, 'business' => true],
            'loans' => ['label' => 'Loan Investments', 'group' => 'Investments', 'description' => 'Loans given, schedules and repayments.', 'personal' => true, 'business' => true],
            'benchmarks' => ['label' => 'Benchmarks', 'group' => 'Investments', 'description' => 'Index benchmarking (advanced).', 'personal' => false, 'business' => false],

            // --- Business ---------------------------------------------------------
            'customers' => ['label' => 'Customers', 'group' => 'Business', 'description' => 'Customer records and billing addresses.', 'personal' => false, 'business' => true],
            'employees' => ['label' => 'Employees', 'group' => 'Business', 'description' => 'Employee records.', 'personal' => false, 'business' => true],
            'billing' => ['label' => 'Billing', 'group' => 'Business', 'description' => 'Invoices and billing.', 'personal' => false, 'business' => true],
            'investor_management' => ['label' => 'Investors & Funds', 'group' => 'Business', 'description' => 'Investor capital and returns.', 'personal' => false, 'business' => true],
        ];
    }
}

if (!function_exists('capistra_feature_key')) {
    function capistra_feature_key(string $key): string
    {
        return 'enable_' . $key;
    }
}

if (!function_exists('capistra_profile_mode')) {
    /** personal | business | hybrid */
    function capistra_profile_mode(): string
    {
        $mode = strtolower((string) capistra_setting('financial_profile_mode', 'personal'));
        return in_array($mode, ['personal', 'business', 'hybrid'], true) ? $mode : 'personal';
    }
}

if (!function_exists('capistra_feature_default')) {
    /** Compute the default enabled state for a module given the profile mode. */
    function capistra_feature_default(string $key): bool
    {
        $registry = capistra_feature_registry();
        if (!isset($registry[$key])) {
            return true;
        }
        $def = $registry[$key];
        return match (capistra_profile_mode()) {
            'business' => (bool) $def['business'],
            'hybrid'   => (bool) ($def['personal'] || $def['business']),
            default    => (bool) $def['personal'],
        };
    }
}

if (!function_exists('capistra_feature_enabled')) {
    /**
     * Is a module enabled? Explicit `app_settings.enable_<key>` wins; otherwise
     * fall back to the profile-aware default.
     */
    function capistra_feature_enabled(string $key): bool
    {
        if (!array_key_exists($key, capistra_feature_registry())) {
            return true; // unknown module: do not block
        }
        $stored = capistra_setting('enable_' . $key, null);
        if ($stored !== null && $stored !== '') {
            return in_array(strtolower($stored), ['1', 'true', 'yes', 'on'], true);
        }
        return capistra_feature_default($key);
    }
}

if (!function_exists('capistra_enabled_features')) {
    /** @return list<string> */
    function capistra_enabled_features(): array
    {
        $enabled = [];
        foreach (array_keys(capistra_feature_registry()) as $key) {
            if (capistra_feature_enabled($key)) {
                $enabled[] = $key;
            }
        }
        return $enabled;
    }
}

if (!function_exists('capistra_guard_module')) {
    /**
     * Block direct URL access to a disabled module. Call at the top of a page,
     * after authentication. Sends a 404-style response (no information leak
     * about whether the module exists).
     */
    function capistra_guard_module(string $key): void
    {
        if (capistra_feature_enabled($key)) {
            return;
        }
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><title>Not available</title><p>This module is not enabled for this installation.</p>';
        exit;
    }
}
