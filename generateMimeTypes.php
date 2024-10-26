<?php

declare(strict_types=1);

require 'vendor/autoload.php';

$mimeTypeInfoMap = new MimeTypeInfoMap();
$scanners = [
    new MimeDbScanner(), new FreeDesktopScanner(),
];

/** @var MimeTypeScannerInterface $scanner */
foreach ($scanners as $scanner) {
    $scanner->scan($mimeTypeInfoMap);
}

MimeTypeInfoCodeGenerator::generateMimeTypeInfoClass($mimeTypeInfoMap);

echo 'MimeTypesConstants.php generated!\n';