<?php

declare(strict_types=1);

defined('ROOT_DIR') or define('ROOT_DIR', realpath(dirname(__DIR__)));

// Load autoloader
$autoload = ROOT_DIR . '/vendor/autoload.php';
if (!is_file($autoload)) {
    die('vendor/autoload.php not found. Run: composer install');
}
require_once $autoload;

try {
    $app = new App();

    if (method_exists($app, 'boot')) {
        $app->boot();
    }

    $app->run();
} catch (Throwable $e) {
    if (class_exists('ErrorHandling')) {
        ErrorHandling::exceptionHandle($e);
    } else {
        throw $e;
    }
}