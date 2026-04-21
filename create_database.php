<?php
/**
 * Standalone script to create the MySQL database (no Composer/Laravel required).
 * Use this if artisan migrate fails due to path encoding issues.
 * Run: php create_database.php
 */

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_DATABASE') ?: 'complaint_app';

// Try to read from .env
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $map = ['HOST' => 'host', 'PORT' => 'port', 'DATABASE' => 'database', 'USERNAME' => 'user', 'PASSWORD' => 'pass'];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (preg_match('/^DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)=(.*)$/', $line, $m)) {
            $val = trim($m[2], " \t\n\r\0\x0B\"'");
            if ($val !== '' && isset($map[$m[1]])) {
                ${$map[$m[1]]} = $val;
            }
        }
    }
}

$dsn = "mysql:host={$host};port={$port};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '{$database}' created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
