<?php

declare(strict_types=1);

// Bootstrap
define('ROOT_DIR', realpath(dirname(__DIR__, 2)));
require_once ROOT_DIR . '/vendor/autoload.php';

try {
    $app = new App();
    $app->boot();

    echo "🔍 Checking uploads/images directory\n";
    echo "=================================\n\n";

    $imagesDir = ROOT_DIR . '/storage/uploads/images';

    if (!is_dir($imagesDir)) {
        echo "❌ Directory not found: $imagesDir\n";
        exit(1);
    }

    // Count all image files
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($imagesDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY,
    );

    $totalFiles = 0;
    $totalSize = 0;
    $filesByAge = [];

    $thirtyDaysAgo = time() - (30 * 86400);
    $sevenDaysAgo = time() - (7 * 86400);

    foreach ($iterator as $file) {
        if ($file->isDir()) {
            continue;
        }

        $totalFiles++;
        $size = $file->getSize();
        $totalSize += $size;

        $modified = $file->getMTime();
        $ageDays = (time() - $modified) / 86400;

        if ($ageDays > 30) {
            $filesByAge['30+ days'][] = $file->getPathname();
        } elseif ($ageDays > 7) {
            $filesByAge['7-30 days'][] = $file->getPathname();
        } else {
            $filesByAge['0-7 days'][] = $file->getPathname();
        }
    }

    echo sprintf("Total files in images/: %d\n", $totalFiles);
    echo sprintf("Total size: %s\n", formatBytes($totalSize));
    echo "\n";

    echo "Files by age:\n";
    foreach ($filesByAge as $ageGroup => $files) {
        echo sprintf("  %s: %d files\n", $ageGroup, count($files));

        if ($ageGroup === '30+ days' && !empty($files)) {
            echo "    Oldest 5 files:\n";
            usort($files, function ($a, $b) {
                return filemtime($a) <=> filemtime($b);
            });
            foreach (array_slice($files, 0, 5) as $filepath) {
                $age = round((time() - filemtime($filepath)) / 86400, 1);
                echo sprintf(
                    "      - %s (%s, %s days)\n",
                    basename($filepath),
                    formatBytes(filesize($filepath)),
                    $age,
                );
            }
        }
    }
} catch (Throwable $e) {
    echo '❌ Error: ' . $e->getMessage() . "\n";
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
