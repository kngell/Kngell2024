<?php

declare(strict_types=1);

require dirname(__DIR__) . '/../vendor/autoload.php';
$scanner = new StatusJsScanner();
$codes = $scanner->scan();

StatusCodeGenerator::generateHttpCodeClasses($codes);
echo "Generated Http Status Code classes.\n";
