<?php

declare(strict_types=1);

// Bootstrap
define('ROOT_DIR', realpath(dirname(__DIR__, 2)));
require_once ROOT_DIR . '/vendor/autoload.php';

try {
    $app = new App();
    $app->boot();

    echo "✅ App booted in CLI mode\n";
    echo "ℹ️  Session and cookies were NOT loaded (not needed for CLI)\n\n";

    // Get logger from container
    // $logger = $app->get(LoggerInterface::class);
    // if (!$logger) {
    //     // Simple fallback logger
    //     $logger = new class () {
    //         public function info($msg, $context = [])
    //         {
    //             echo "[INFO] $msg\n";
    //         }

    //         public function error($msg, $context = [])
    //         {
    //             echo "[ERROR] $msg\n";
    //         }

    //         public function warning($msg, $context = [])
    //         {
    //             echo "[WARNING] $msg\n";
    //         }
    //     };
    // }

    // Get models through DI - they'll have all their dependencies
    $productModel = $app->get(ProductModel::class);
    $galleryModel = $app->get(ProductImageGalleryModel::class);

    if (!$productModel || !$galleryModel) {
        echo "❌ Models not found in container\n";
        exit(1);
    }

    echo "✅ Models loaded with dependencies resolved\n\n";

    // Create your services
    require_once ROOT_DIR . '/App/Services/DatabaseFilePathService.php';
    require_once ROOT_DIR . '/App/Services/OrphanFileFinderService.php';

    $databaseFilePaths = new DatabaseFilePathService($productModel, $galleryModel, $logger);
    $finder = new OrphanFileFinderService($databaseFilePaths, $logger);

    // Run
    $uploadsDir = realpath(ROOT_DIR . '/storage/uploads');

    if (!$uploadsDir || !is_dir($uploadsDir)) {
        echo "❌ Error: Uploads directory not found\n";
        exit(1);
    }

    echo "🔍 Finding Orphan Files\n";
    echo "Directory: {$uploadsDir}\n\n";

    $orphans = $finder->findOrphanFiles($uploadsDir);

    if (empty($orphans)) {
        echo "✅ No orphan files found!\n";
        exit(0);
    }

    echo 'Found ' . count($orphans) . " orphan files:\n";
    foreach ($orphans as $orphan) {
        echo sprintf(
            "  - %s (%s)\n",
            basename($orphan['path']),
            formatBytes($orphan['size']),
        );
    }
} catch (Throwable $e) {
    echo '❌ Error: ' . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}

function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
