<?php

declare(strict_types=1);

defined('ROOT_DIR') or define('ROOT_DIR', realpath(dirname(__DIR__)));

$autoload = ROOT_DIR . '/vendor/autoload.php';
if (is_file($autoload)) {
    $autoload = require_once $autoload;
}

try {
    $app = App::getInstance();
    $app->boot()->run();
} catch (Throwable $e) {
    ErrorHandling::exceptionHandle($e);
}