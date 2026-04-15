@echo off
chcp 65001 >nul
echo Trying to run migrate using Windows short path (8.3)...
echo.

cd /d "%~dp0"
for %%I in (.) do set SHORTPATH=%%~sI
echo Short path: %SHORTPATH%

cd /d "%SHORTPATH%"

echo Creating database...
php create_database.php

echo Running migrations...
php artisan migrate --force

echo.
pause
