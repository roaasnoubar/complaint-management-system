# Fix: PHP Fatal Error with Arabic Path

If you see `Failed to open stream: No such file or directory` or encoding errors when running `php artisan migrate`, the project path contains Arabic/non-ASCII characters that cause PHP and Composer to fail.

## Solution 1: RUN_MIGRATE.bat (Recommended - No Copy Required)

**Double-click** `RUN_MIGRATE.bat` in the project folder.

This will:
1. Create a virtual drive **W:** that maps to your project (avoids Arabic in path)
2. Create the `complaint_app` database in MySQL
3. Regenerate Composer autoload with ASCII paths
4. Run migrations

**After it completes**, use the project via drive W:
```
cd /d W:\
php artisan serve
```

Or double-click `START_APP.bat` to start the server. The W: drive stays until you restart.

## Solution 2: Copy Project to Clean Path

**Double-click** `migrate_from_clean_path.bat` in the project folder.

This copies the project to `C:\complaint-app` and runs migrations there. Use the project from `C:\complaint-app` afterward.

## Solution 3: Try Short Path First

**Double-click** `run_migrate_shortpath.bat`

This uses Windows 8.3 short path (e.g. `C:\ADARA~1\COMPLA~1`) which may avoid encoding issues. If it works, you can continue using the project from its current location.

## Solution 4: Manual Steps

1. **Create the database** (run from project folder):
   ```
   php create_database.php
   ```

2. **Move the project** to a path without Arabic:
   - Copy the entire `complaint-app` folder to `C:\complaint-app`
   - Open Command Prompt
   - Run:
   ```
   cd C:\complaint-app
   composer dump-autoload
   php artisan migrate --force
   php artisan db:seed
   ```

3. **Use the project** from `C:\complaint-app` from now on.

## Why This Happens

The path `ادارة الشكاوي` (Arabic) gets corrupted when PHP/Composer resolves file paths on Windows. Moving to an ASCII-only path like `C:\complaint-app` resolves this.
