@echo off
chcp 65001 >nul
echo ============================================
echo  Complaint App - Migrate from Clean Path
echo ============================================
echo.
echo This script copies the project to C:\complaint-app (avoids Arabic path issues)
echo and runs migrations there.
echo.

set TARGET=C:\complaint-app
set SOURCE=%~dp0

echo Copying project to %TARGET%...
robocopy "%SOURCE%" "%TARGET%" /E /XD .git node_modules /NFL /NDL /NJH /NJS /nc /ns /np
if %ERRORLEVEL% GTR 7 (
    echo Robocopy failed. Trying xcopy...
    mkdir "%TARGET%" 2>nul
    xcopy "%SOURCE%*" "%TARGET%\" /E /I /Y /EXCLUDE:%SOURCE%migrate_from_clean_path.bat >nul
)

echo.
echo Changing to %TARGET%
cd /d "%TARGET%"

echo Creating database...
php create_database.php

echo.
echo Regenerating Composer autoload (new paths)...
call composer dump-autoload 2>nul

echo.
echo Running migrations...
php artisan migrate --force

echo.
echo Done! You can now use the project from %TARGET%
echo Run: cd %TARGET%
echo      php artisan serve
echo.
pause
