@echo off
chcp 65001 >nul
:: Run this AFTER RUN_MIGRATE.bat has created the W: drive
if not exist W:\ (
    echo Run RUN_MIGRATE.bat first to create the W: drive.
    pause
    exit /b 1
)
cd /d W:\
php artisan serve
pause
