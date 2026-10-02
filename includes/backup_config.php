<?php
declare(strict_types=1);

/**
 * Capistra - configuration for the legacy backup helpers.
 *
 * These scripts originally read a local, git-ignored `includes/config.ini`.
 * The public build keeps that file optional (an operator may still create it
 * to override the backup tuning), but the database connection always comes
 * from the canonical `.env`, so the Backup feature works on a fresh clone with
 * no local-only files present.
 */

require_once dirname(__DIR__) . '/config/app.php';

if (!function_exists('capistra_backup_config')) {
    /**
     * @return array{host:string,port:string,user:string,password:string,name:string,timezone:string,max_files:int,mysqldump_path:string,backup_interval_hours:int}
     */
    function capistra_backup_config(): array
    {
        $file = __DIR__ . '/config.ini';
        $ini  = is_readable($file) ? (parse_ini_file($file, true) ?: []) : [];

        $clean = static fn ($value): string => trim((string) $value, "\"'\r\n\t ");

        $timezone = $clean($ini['backup']['timezone'] ?? '');
        if ($timezone === '') {
            $timezone = (string) capistra_env('APP_TIMEZONE', 'Asia/Kathmandu');
        }

        $dump = $clean($ini['backup']['mysqldump_path'] ?? '');
        if ($dump === '' || !@is_file($dump)) {
            $dump = capistra_backup_default_mysqldump();
        }

        return [
            'host'                  => DB_HOST,
            'port'                  => DB_PORT,
            'user'                  => DB_USER,
            'password'              => DB_PASSWORD,
            'name'                  => DB_NAME,
            'timezone'              => $timezone,
            'max_files'             => isset($ini['backup']['max_files']) ? (int) $ini['backup']['max_files'] : 7,
            'mysqldump_path'        => $dump,
            'backup_interval_hours' => isset($ini['backup']['backup_interval_hours']) ? (int) $ini['backup']['backup_interval_hours'] : 24,
        ];
    }
}

if (!function_exists('capistra_backup_default_mysqldump')) {
    /** Locate mysqldump: prefer a XAMPP/sibling MySQL install, else rely on PATH. */
    function capistra_backup_default_mysqldump(): string
    {
        $candidates = [
            'C:/xampp/mysql/bin/mysqldump.exe',
            dirname(PHP_BINARY) . '/../mysql/bin/mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ];
        foreach ($candidates as $candidate) {
            if (@is_file($candidate)) {
                return $candidate;
            }
        }
        return 'mysqldump';
    }
}
