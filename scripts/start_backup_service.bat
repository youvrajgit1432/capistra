 @echo off
:: GMIC Backup Service Installer
:: Must be run as Administrator

:: Configuration
SET XAMPP_ROOT=C:\xampp
SET PROJECT_ROOT=%XAMPP_ROOT%\htdocs\gmic
SET NSSM_BIN=%PROJECT_ROOT%\scripts\nssm.exe
SET SERVICE_NAME=GMICBackup
SET PHP_BIN=%XAMPP_ROOT%\php\php.exe
SET BACKUP_SCRIPT=%PROJECT_ROOT%\backup.php

:: Verify paths
if not exist "%PROJECT_ROOT%\backups\" (
    mkdir "%PROJECT_ROOT%\backups"
    mkdir "%PROJECT_ROOT%\backups\logs"
)

:: Download NSSM if not present
if not exist "%NSSM_BIN%" (
    echo Downloading NSSM...
    powershell -Command "Invoke-WebRequest -Uri 'https://nssm.cc/ci/nssm-2.24-101-g897c7ad.zip' -OutFile '%TEMP%\nssm.zip'"
    powershell -Command "Expand-Archive -Path '%TEMP%\nssm.zip' -DestinationPath '%TEMP%\nssm'"
    copy "%TEMP%\nssm\nssm-2.24-101-g897c7ad\win64\nssm.exe" "%NSSM_BIN%"
    if errorlevel 1 (
        echo Failed to install NSSM
        pause
        exit /b 1
    )
)

:: Check if service already exists
"%NSSM_BIN%" status %SERVICE_NAME% >nul 2>&1
if %errorlevel% equ 0 (
    echo Service already exists. Stopping and removing...
    "%NSSM_BIN%" stop %SERVICE_NAME%
    "%NSSM_BIN%" remove %SERVICE_NAME% confirm
    timeout /t 3 >nul
)

:: Install service
echo Installing service...
"%NSSM_BIN%" install %SERVICE_NAME% "%PHP_BIN%" "%BACKUP_SCRIPT% --scheduled"
if %errorlevel% neq 0 (
    echo Failed to install service
    pause
    exit /b 1
)

:: Configure service
"%NSSM_BIN%" set %SERVICE_NAME% AppDirectory "%PROJECT_ROOT%"
"%NSSM_BIN%" set %SERVICE_NAME% DisplayName "GMIC Auto Backup"
"%NSSM_BIN%" set %SERVICE_NAME% Description "Automated database backup service for GMIC"
"%NSSM_BIN%" set %SERVICE_NAME% AppStdout "%PROJECT_ROOT%\backups\logs\service.log"
"%NSSM_BIN%" set %SERVICE_NAME% AppStderr "%PROJECT_ROOT%\backups\logs\error.log"
"%NSSM_BIN%" set %SERVICE_NAME% Start SERVICE_AUTO_START

:: Start service
echo Starting service...
"%NSSM_BIN%" start %SERVICE_NAME%
if %errorlevel% neq 0 (
    echo Failed to start service
    pause
    exit /b 1
)

echo.
echo GMIC Backup Service installed and started successfully
echo.
echo To manage the service:
echo   net start %SERVICE_NAME%
echo   net stop %SERVICE_NAME%
echo   sc delete %SERVICE_NAME%
echo.
pause