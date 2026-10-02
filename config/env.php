<?php
declare(strict_types=1);

/**
 * Capistra - minimal, dependency-free .env loader.
 *
 * Loads key=value pairs into the process environment (and a returned array).
 * Designed so the application has a single source of configuration and never
 * needs to ship real credentials. Real values live in a git-ignored `.env`
 * file created from `.env.example`.
 */

if (!function_exists('capistra_env')) {
    /**
     * Read an environment value with a fallback.
     */
    function capistra_env(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }

        // Normalise common boolean spellings.
        return match (strtolower($value)) {
            'true', '(true)'   => 'true',
            'false', '(false)' => 'false',
            'null', '(null)'   => null,
            'empty', '(empty)' => '',
            default            => $value,
        };
    }
}

if (!function_exists('capistra_load_env')) {
    /**
     * Parse a .env file and load its values. Existing process env wins,
     * so real deployment environment variables always take precedence.
     */
    function capistra_load_env(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip a single pair of surrounding quotes.
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last  = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if ($key === '' || getenv($key) !== false) {
                continue;
            }

            putenv("$key=$value");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
}
