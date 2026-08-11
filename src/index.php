<?php

declare(strict_types=1);

// ==========================================================
// BOOTSTRAP LAYER
// ==========================================================

defined('ROOT_DIR') or define('ROOT_DIR', realpath(dirname(__DIR__)));
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

// Define LOG_DIR BEFORE App exists
defined('LOG_DIR') or define(
    'LOG_DIR',
    ROOT_DIR . DS . 'storage' . DS . 'logs',
);

// Ensure log directory exists
if (!is_dir(LOG_DIR)) {
    @mkdir(LOG_DIR, 0755, true);
}

// Autoload
$autoload = ROOT_DIR . DS . 'vendor' . DS . 'autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    die('vendor/autoload.php not found. Run: composer install');
}
require_once $autoload;

// Minimal bootstrap error handlers
set_error_handler(function (
    int $severity,
    string $message,
    string $file,
    int $line,
) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e) {
    error_log(sprintf(
        '[Bootstrap Error] %s: %s in %s:%d',
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
    ));

    http_response_code(500);
    echo '<h1>Application Bootstrap Failed</h1>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
});

// ==========================================================
// APPLICATION LAYER
// ==========================================================

$app = new App();   // Safe — bootstrap handler active
$app->boot();       // Replaces handlers with full ErrorHandling
$app->run();