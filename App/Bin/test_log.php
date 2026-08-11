<?php

declare(strict_types=1);
// Force log to a specific file we can control
$testLog = __DIR__ . '/storage/logs/test.log';

// Make sure directory exists
$dir = dirname($testLog);
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Write test message
file_put_contents($testLog, '=== Test at ' . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);

// Test error_log() destination
error_log('Test message 1 - ' . date('Y-m-d H:i:s'));

// Test with explicit destination
error_log('Test message 2 - ' . date('Y-m-d H:i:s'), 3, $testLog);
define('ROOT', '/home/kngell/projects/kngell-ecom');
define('DS', DIRECTORY_SEPARATOR);
// Test CustomLogger
require_once ROOT . '/Framework/Components/Logger/CustomLogger.class.php';

define('STORAGE', ROOT . '/storage/');

$logger = new CustomLogger(
    STORAGE . 'logs' . DS . 'app.log',
    true,
    3,
    true,
);

$logger->info('Test from test_log.php');

echo "Tests completed. Check:\n";
echo '1. ' . $testLog . "\n";
echo '2. ' . STORAGE . 'logs' . DS . 'app.log' . "\n";
echo "3. Apache error log: /var/log/apache2/error.log\n";