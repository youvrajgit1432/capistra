<?php
declare(strict_types=1);

/**
 * Capistra - application settings store.
 *
 * Scalar preferences live in the `app_settings` key/value table. This file adds
 * typed accessors, a small default catalogue, and a single, documented
 * precedence model so the application no longer has two competing sources of
 * truth (see `capistra_base_currency()`).
 *
 * Precedence for scalar settings:
 *   1. `app_settings` row (runtime, operator-editable)      <- wins
 *   2. environment variable / `config/app.php` constant     <- deployment default
 *   3. hard-coded default in this file                      <- last resort
 *
 * IMPORTANT: `app_settings` is for *scalar preferences only*. Financial master
 * data (categories, accounts, securities, prices, budgets, ...) is normalised
 * into dedicated relational tables and must NEVER be serialised here.
 */

require_once __DIR__ . '/app.php';

if (!function_exists('capistra_setting_defaults')) {
    /**
     * Catalogue of known scalar settings with safe defaults.
     *
     * @return array<string,string>
     */
    function capistra_setting_defaults(): array
    {
        return [
            // Brand / organisation
            'display_name'            => APP_NAME,
            'company_name'            => APP_NAME,
            'company_address'         => '',
            'company_tax_id'          => '',

            // Profile
            'financial_profile_mode'  => 'personal', // personal | business | hybrid

            // Money
            'base_currency'           => APP_BASE_CURRENCY,
            'currency_symbol_style'   => 'prefix',   // prefix | suffix
            'number_grouping'         => 'international', // international | south_asian
            'decimal_precision'       => '2',

            // Locale / time
            'timezone'                => 'Asia/Kathmandu',
            'date_format'             => 'Y-m-d',
            'date_display_mode'       => 'AD',       // AD | BS | both

            // Fiscal / reporting
            'fiscal_year_start'       => '07-01',    // MM-DD
            'default_reporting_period'=> 'fiscal_year',
            'dashboard_landing'       => 'accounting/index.php',

            // Appearance
            'theme'                   => 'light',
            'density'                 => 'comfortable',

            // Insights thresholds
            'low_cash_threshold'      => '0',
            'portfolio_concentration_limit' => '35',

            // Investment / NEPSE defaults
            'nepse_country'           => 'Nepal',
            'nepse_currency'          => 'NPR',
            'market_data_source'      => 'manual',
        ];
    }
}

if (!function_exists('capistra_settings')) {
    /**
     * Load all settings once per request (cached in a static array).
     *
     * @return array<string,string>
     */
    function capistra_settings(bool $refresh = false): array
    {
        static $cache = null;
        if ($cache !== null && !$refresh) {
            return $cache;
        }

        $cache = capistra_setting_defaults();
        try {
            $pdo = capistra_pdo();
            $rows = $pdo->query('SELECT setting_key, setting_value FROM app_settings')->fetchAll();
            foreach ($rows as $row) {
                $key = (string) $row['setting_key'];
                if ($key === '') {
                    continue;
                }
                $cache[$key] = (string) ($row['setting_value'] ?? '');
            }
        } catch (Throwable $e) {
            // Missing table / DB unavailable: fall back to defaults so pages that
            // only need branding still render. Real errors surface elsewhere.
            error_log('capistra_settings: ' . $e->getMessage());
        }

        return $cache;
    }
}

if (!function_exists('capistra_setting')) {
    /** Read one setting with precedence: DB row > default catalogue. */
    function capistra_setting(string $key, ?string $default = null): ?string
    {
        $all = capistra_settings();
        if (array_key_exists($key, $all) && $all[$key] !== '') {
            return $all[$key];
        }
        return $default ?? ($all[$key] ?? null);
    }
}

if (!function_exists('capistra_setting_bool')) {
    function capistra_setting_bool(string $key, bool $default = false): bool
    {
        $value = capistra_setting($key, $default ? 'true' : 'false');
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('capistra_setting_int')) {
    function capistra_setting_int(string $key, int $default = 0): int
    {
        $value = capistra_setting($key, (string) $default);
        return is_numeric($value) ? (int) $value : $default;
    }
}

if (!function_exists('capistra_set_setting')) {
    /**
     * Persist a scalar setting (upsert). Returns true on success.
     * Callers are responsible for audit logging of meaningful changes.
     */
    function capistra_set_setting(PDO $pdo, string $key, string $value): bool
    {
        $stmt = $pdo->prepare(
            'INSERT INTO app_settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $ok = $stmt->execute([':k' => $key, ':v' => $value]);

        // Refresh the per-request cache so subsequent reads see the change.
        capistra_settings(true);

        return (bool) $ok;
    }
}

if (!function_exists('capistra_base_currency')) {
    /**
     * The single source of truth for the reporting currency.
     * DB setting wins; otherwise the deployment default from the environment.
     */
    function capistra_base_currency(): string
    {
        $value = capistra_setting('base_currency', null);
        if ($value === null || trim($value) === '') {
            $value = APP_BASE_CURRENCY;
        }
        return strtoupper(trim($value));
    }
}

if (!function_exists('capistra_currency_symbol')) {
    /** Return the display symbol for a currency code (UTF-8 safe). */
    function capistra_currency_symbol(string $code): string
    {
        return match (strtoupper($code)) {
            'NPR'   => 'Rs.',
            'USD'   => '$',
            'EUR'   => '€',
            'GBP'   => '£',
            'INR'   => '₹',
            'AUD'   => 'A$',
            'CAD'   => 'C$',
            'JPY'   => '¥',
            'CNY'   => '¥',
            default => strtoupper($code),
        };
    }
}

if (!function_exists('capistra_money')) {
    /**
     * Canonical money formatter. Honours currency, symbol position, grouping
     * and decimal precision from settings. Always returns UTF-8 text.
     */
    function capistra_money(float|int|string $amount, ?string $code = null): string
    {
        $code     = strtoupper($code ?? capistra_base_currency());
        $decimals = max(0, min(4, capistra_setting_int('decimal_precision', 2)));
        $grouping = (string) capistra_setting('number_grouping', 'international');

        if ($grouping === 'south_asian') {
            $formatted = capistra_format_south_asian((float) $amount, $decimals);
        } else {
            $formatted = number_format((float) $amount, $decimals, '.', ',');
        }

        $symbol = capistra_currency_symbol($code);
        if ((string) capistra_setting('currency_symbol_style', 'prefix') === 'suffix') {
            return $formatted . ' ' . $symbol;
        }
        return $symbol . ' ' . $formatted;
    }
}

if (!function_exists('capistra_format_south_asian')) {
    /** Group digits in the south-Asian (lakh/crore) style: 12,34,567.89 */
    function capistra_format_south_asian(float $amount, int $decimals = 2): string
    {
        $negative = $amount < 0;
        $amount   = abs($amount);
        $whole    = (string) (int) floor($amount);
        $fraction = $decimals > 0 ? substr(number_format($amount - floor($amount), $decimals, '.', ''), 1) : '';

        if (strlen($whole) > 3) {
            $last3 = substr($whole, -3);
            $rest  = substr($whole, 0, -3);
            $rest  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $whole = $rest . ',' . $last3;
        }

        return ($negative ? '-' : '') . $whole . $fraction;
    }
}

if (!function_exists('capistra_fiscal_year_start')) {
    /** Fiscal year start as 'MM-DD'. */
    function capistra_fiscal_year_start(): string
    {
        $value = (string) capistra_setting('fiscal_year_start', '07-01');
        return preg_match('/^\d{2}-\d{2}$/', $value) ? $value : '07-01';
    }
}

if (!function_exists('capistra_fiscal_year_bounds')) {
    /**
     * Given a reference date, return the [start, end] dates of the fiscal year
     * containing it. Pure date math, no timezone surprises.
     *
     * @return array{0:string,1:string}
     */
    function capistra_fiscal_year_bounds(?string $reference = null): array
    {
        $ref   = $reference ? strtotime($reference) : time();
        $year  = (int) date('Y', $ref);
        [$m, $d] = array_map('intval', explode('-', capistra_fiscal_year_start()));

        $startThisYear = mktime(0, 0, 0, $m, $d, $year);
        if ($ref >= $startThisYear) {
            $start = $startThisYear;
            $end   = mktime(0, 0, 0, $m, $d - 1, $year + 1);
        } else {
            $start = mktime(0, 0, 0, $m, $d, $year - 1);
            $end   = mktime(0, 0, 0, $m, $d - 1, $year);
        }

        return [date('Y-m-d', (int) $start), date('Y-m-d', (int) $end)];
    }
}

if (!function_exists('capistra_apply_timezone')) {
    function capistra_apply_timezone(): void
    {
        $tz = (string) capistra_setting('timezone', 'Asia/Kathmandu');
        if ($tz !== '' && in_array($tz, timezone_identifiers_list(), true)) {
            date_default_timezone_set($tz);
        }
    }
}
