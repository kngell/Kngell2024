<?php

// /home/kngell/projects/kngell-ecom/App/Bin/cli-bootstrap.php

declare(strict_types=1);

// Check CLI
if (PHP_SAPI !== 'cli') {
    die('This script must be run from the command line');
}

// Define constants exactly like your web front controller
define('ROOT_DIR', dirname(__DIR__, 2));
define('DS', DIRECTORY_SEPARATOR);
define('IS_CLI', true);

// Set up minimal server environment to satisfy AppConfig
$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'CLI-Script/1.0';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/cli';
$_SERVER['SCRIPT_NAME'] = '/cli.php';

// Required for AppConfig error handling
$_SERVER['error_handler'] = [
    'display_errors' => 1,
    'error_reporting' => E_ALL,
    'log_errors' => 1,
    'error_log' => ROOT_DIR . '/var/log/php-cli-errors.log',
    'error' => null, // Will be set by AppConfig
    'exception' => null, // Will be set by AppConfig
];

// Ensure log directory exists
$logDir = ROOT_DIR . '/var/log';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Load autoloader
require_once ROOT_DIR . '/vendor/autoload.php';

/**
 * Bootstrap the application for CLI.
 */
function bootstrapCliApp(): App
{
    echo "🚀 Bootstrapping application for CLI...\n";

    // Create App instance - this will trigger AppConfig setup
    $app = new App();

    // Boot the application (this loads cache, sessions, etc.)
    $app->boot();

    // Skip some web-specific boot methods if they cause issues
    // We don't need sessions/cookies for cache cleanup

    echo "✅ Application bootstrapped\n";

    return $app;
}

/**
 * Handle CLI errors.
 */
function handleCliError(Throwable $e): void
{
    echo '❌ Error: ' . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";

    // Add more details in verbose mode
    if (in_array('--verbose', $GLOBALS['argv'] ?? []) || in_array('-v', $GLOBALS['argv'] ?? [])) {
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }

    exit(1);
}

// Set up error handling
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler('handleCliError');