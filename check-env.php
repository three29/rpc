<?php
/**
 * Environment Variable Diagnostic Tool
 *
 * Run this to check if your .env file is being loaded correctly
 *
 * Usage: php check-env.php /path/to/your/app/root
 */

if ($argc < 2) {
    echo "Usage: php check-env.php /path/to/app/root\n";
    exit(1);
}

$rootPath = $argv[1];
$envFile = $rootPath . '/config/.env';

echo "=== Environment Diagnostic Tool ===\n\n";

// Check if .env file exists
echo "1. Checking .env file...\n";
echo "   Path: $envFile\n";
if (!file_exists($envFile)) {
    echo "   ❌ .env file NOT FOUND!\n";
    echo "   Create a .env file at: $envFile\n\n";
    exit(1);
} else {
    echo "   ✅ .env file exists\n";
    echo "   Size: " . filesize($envFile) . " bytes\n\n";
}

// Load dotenv
echo "2. Loading .env file...\n";
require_once $rootPath . '/vendor/autoload.php';

try {
    $dotenv = \Dotenv\Dotenv::createImmutable($rootPath . '/config');
    $dotenv->safeLoad();
    echo "   ✅ Dotenv loaded successfully\n\n";
} catch (Exception $e) {
    echo "   ❌ Error loading dotenv: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Check database variables
echo "3. Checking database environment variables...\n";
$dbVars = [
    'DB_ADAPTER',
    'DB_HOSTNAME',
    'DB_NAME',
    'DB_USERNAME',
    'DB_PASSWORD',
    'DB_PORT',
    'DB_SOCKET',
    'DB_PREFIX'
];

$missing = [];
foreach ($dbVars as $var) {
    $valueEnv = $_ENV[$var] ?? null;
    $valueGetenv = getenv($var);

    if (!$valueEnv && !$valueGetenv) {
        echo "   ❌ $var: NOT SET\n";
        $missing[] = $var;
    } else {
        $value = $valueEnv ?: $valueGetenv;
        // Hide password
        if ($var === 'DB_PASSWORD') {
            $display = $value ? '***' : '(empty)';
        } else {
            $display = $value ?: '(empty)';
        }
        echo "   ✅ $var: $display\n";
    }
}

echo "\n";

// Summary
if (count($missing) > 0) {
    echo "⚠️  Missing variables: " . implode(', ', $missing) . "\n";
    echo "Add these to your .env file: $envFile\n\n";
} else {
    echo "✅ All database variables are set!\n\n";
}

// Check APP_ENV
$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV');
echo "4. Application Environment\n";
echo "   APP_ENV: " . ($appEnv ?: 'NOT SET') . "\n\n";

echo "=== Diagnostic Complete ===\n";
