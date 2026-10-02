 @echo off
:: Capistra Auto Backup Script
:: Called by Windows Task Scheduler. Adjust PROJECT_ROOT for your install.

:: Set paths
set XAMPP_ROOT=C:\xampp
set PHP_BIN="%XAMPP_ROOT%\php\php.exe"
set BACKUP_SCRIPT="%XAMPP_ROOT%\htdocs\capistra\backup.php"
set LOG_DIR="%XAMPP_ROOT%\htdocs\capistra\backups\logs"

:: Create log directory if it doesn't exist
if not exist %LOG_DIR% mkdir %LOG_DIR%

:: Run the backup and log results
echo [%date% %time%] Starting backup >> %LOG_DIR%\backup.log
%PHP_BIN% %BACKUP_SCRIPT% >> %LOG_DIR%\backup.log 2>> %LOG_DIR%\error.log

:: Check exit code
if %errorlevel% equ 0 (
    echo [%date% %time%] Backup completed successfully >> %LOG_DIR%\backup.log
) else (
    echo [%date% %time%] Backup failed with error %errorlevel% >> %LOG_DIR%\error.log
)

exit /b %errorlevel%