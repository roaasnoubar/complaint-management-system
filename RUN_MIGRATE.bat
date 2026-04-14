@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

echo ============================================
echo  Complaint App - Fix Path & Run Migrate
echo ============================================
echo.

:: Get project folder (remove trailing backslash)
set "PROJECT=%~dp0"
if "%PROJECT:~-1%"=="\" set "PROJECT=%PROJECT:~0,-1%"

:: Use drive W: for virtual path (ASCII only)
set "DRIVE=W:"

echo Step 1: Creating virtual drive %DRIVE% -> project folder
subst %DRIVE% "%PROJECT%" 2>nul
if errorlevel 1 (
    subst %DRIVE% /d 2>nul
    timeout /t 2 >nul
    subst %DRIVE% "%PROJECT%"
)

echo Step 2: Switching to %DRIVE%\
cd /d %DRIVE%\

echo Step 3: Creating database...
php create_database.php
if errorlevel 1 (
    echo Database creation failed. Make sure MySQL is running in XAMPP.
    goto cleanup
)

echo Step 4: Regenerating Composer autoload with ASCII paths...
call composer dump-autoload --no-interaction 2>nul

echo Step 5: Running migrations...
php artisan migrate --force

echo.
if errorlevel 1 (
    echo Migration had errors.
) else (
    echo SUCCESS! Migrations completed.
    echo.
    echo To run the app from the virtual drive:
    echo   cd /d %DRIVE%\
    echo   php artisan serve
    echo.
    echo Or copy project to C:\complaint-app for permanent use.
)

:cleanup
echo.
echo NOTE: Virtual drive %DRIVE% is still active - use it to run the app:
echo   cd /d %DRIVE%\
echo   php artisan serve
echo.
echo The drive will be removed when you restart. Or run: subst %DRIVE% /d
echo.
pause
exit /b 0
