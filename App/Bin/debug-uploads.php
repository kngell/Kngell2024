<?php

declare(strict_types=1);

// Bootstrap
define('ROOT_DIR', realpath(dirname(__DIR__, 2)));
require_once ROOT_DIR . '/vendor/autoload.php';

try {
    $app = new App();
    $app->boot();

    echo "🔍 Debug Analysis\n";
    echo "===============\n\n";

    require_once ROOT_DIR . '/Framework/Components/Files/src/FileUpload/CleanUp/DatabaseFilePathService.class.php';
    require_once ROOT_DIR . '/Framework/Components/Files/src/FileUpload/CleanUp/OrphanFileFinderService.class.php';

    $productModel = $app->get(ProductModel::class);
    $galleryModel = $app->get(ProductImageGalleryModel::class);
    $databaseFilePaths = new DatabaseFilePathService($productModel, $galleryModel, null);
    $finder = new OrphanFileFinderService($databaseFilePaths, null);

    $uploadsDir = realpath(ROOT_DIR . '/storage/uploads');

    // 1. Get valid paths from database
    echo "📊 Database Analysis:\n";
    $validPaths = $databaseFilePaths->getValidFilePaths();
    echo sprintf("Valid URLs in database: %d\n", count($validPaths));
    if (!empty($validPaths)) {
        echo "First few valid paths:\n";
        foreach (array_slice($validPaths, 0, 5) as $path) {
            echo '  - ' . $path . "\n";
        }
        if (count($validPaths) > 5) {
            echo '  ... and ' . (count($validPaths) - 5) . " more\n";
        }
    }
    echo "\n";

    // 2. Count all files manually
    echo "📁 Manual File Count:\n";
    $totalFiles = 0;
    $tempFiles = 0;
    $excludedFiles = 0;
    $excludedList = ['.htaccess', '.gitkeep', '.gitignore', 'index.html', 'index.php'];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($uploadsDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY,
    );

    foreach ($iterator as $file) {
        if ($file->isDir()) {
            continue;
        }

        $totalFiles++;
        $filename = $file->getFilename();
        $relativePath = str_replace($uploadsDir . '/', '', $file->getPathname());

        if (in_array($filename, $excludedList)) {
            $excludedFiles++;
        } elseif (strpos($relativePath, 'temp/') === 0) {
            $tempFiles++;
        }
    }

    echo sprintf("Total files: %d\n", $totalFiles);
    echo sprintf("Temp files: %d\n", $tempFiles);
    echo sprintf("Excluded files: %d\n", $excludedFiles);
    echo sprintf("Regular files: %d\n", $totalFiles - $tempFiles - $excludedFiles);
    echo "\n";

    // 3. Find orphans
    echo "🔍 Orphan Analysis:\n";
    $orphans = $finder->findOrphanFiles($uploadsDir);
    echo sprintf("Orphans found: %d\n", count($orphans));

    // Group by directory
    $byDir = [];
    foreach ($orphans as $orphan) {
        $dir = dirname($orphan['relative_path']);
        $byDir[$dir] = ($byDir[$dir] ?? 0) + 1;
    }

    echo "Orphans by directory:\n";
    foreach ($byDir as $dir => $count) {
        echo sprintf("  %s: %d files\n", $dir ?: '/', $count);
    }
    echo "\n";

    // 4. Check for files that should be valid
    echo "✅ Files that SHOULD be in database (checking first 5 orphans):\n";
    $checked = 0;
    foreach ($orphans as $orphan) {
        if ($checked >= 5) {
            break;
        }

        $fullPath = $orphan['path'];
        $relativePath = $orphan['relative_path'];

        // Check if file exists in filesystem
        $exists = file_exists($fullPath) ? 'YES' : 'NO';

        echo sprintf("File: %s\n", basename($fullPath));
        echo sprintf("  Path: %s\n", $relativePath);
        echo sprintf("  Exists: %s\n", $exists);
        echo sprintf("  Size: %s\n", formatBytes($orphan['size']));

        // Try to guess if this looks like a valid product image
        if (preg_match('/card\d+_[\da-f]+\.(png|jpg|jpeg|gif|svg)$/i', basename($fullPath))) {
            echo "  Looks like: Product image\n";
        }

        echo "\n";
        $checked++;
    }

    // 5. Let's see if any files in uploads root are counted
    echo "📂 Files in uploads root directory:\n";
    $rootFiles = glob($uploadsDir . '/*');
    $count = 0;
    foreach ($rootFiles as $file) {
        if (is_file($file)) {
            $filename = basename($file);
            if (!in_array($filename, $excludedList)) {
                echo sprintf("  - %s (%s)\n", $filename, formatBytes(filesize($file)));
                $count++;
            }
        }
    }
    echo sprintf("Total non-excluded root files: %d\n", $count);
} catch (Throwable $e) {
    echo '❌ Error: ' . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";
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
