<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

// Exit if not running in CLI
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

// Bootstrap
define('ROOT_DIR', realpath(dirname(__DIR__, 2)));
require_once ROOT_DIR . '/vendor/autoload.php';

try {
    $app = new App();
    $app->boot();

    echo "✅ App booted in CLI mode\n";
    echo "ℹ️  Session and cookies were NOT loaded (not needed for CLI)\n\n";

    // Get cleanup service from container
    $cleanupService = $app->get(OrphanFileCleanupService::class);

    if (!$cleanupService) {
        // Try to create it with dependencies if not in container
        require_once ROOT_DIR . '/Framework/Components/Files/src/FileUpload/CleanUp/DatabaseFilePathService.class.php';
        require_once ROOT_DIR . '/Framework/Components/Files/src/FileUpload/CleanUp/OrphanFileFinderService.class.php';
        require_once ROOT_DIR . '/Framework/Components/Files/src/FileUpload/CleanUp/OrphanFileCleanupService.class.php';
        require_once ROOT_DIR . '/Framework/Components/Files/src/FileUpload/CleanUp/CleanupResult.class.php';
        require_once ROOT_DIR . '/Framework/Components/Files/src/FileUpload/FileOperationsManager.class.php';

        $productModel = $app->get(ProductModel::class);
        $galleryModel = $app->get(ProductImageGalleryModel::class);
        $heroModel = $app->get(HeroModel::class);
        $smallBannerModel = $app->get(SmallBannerModel::class);

        if (!$productModel || !$galleryModel || !$heroModel || !$smallBannerModel) {
            echo "❌ Models not found in container\n";
            exit(1);
        }

        // Create the required dependencies
        $databaseFilePaths = new DatabaseFilePathService($productModel, $galleryModel, $heroModel, $smallBannerModel, null);
        $databaseFilePaths->clearCache();
        $finder = new OrphanFileFinderService($databaseFilePaths, null);
        $fileOperations = $app->get(FileOperationsManager::class);
        $logger = $app->get(LoggerInterface::class) ?? new class () {
            public function info($msg, $context = [])
            {
                echo "[INFO] $msg\n";
            }

            public function error($msg, $context = [])
            {
                echo "[ERROR] $msg\n";
            }

            public function warning($msg, $context = [])
            {
                echo "[WARNING] $msg\n";
            }
        };

        $cleanupService = new OrphanFileCleanupService($finder, $fileOperations, $logger);
    }

    echo "✅ Cleanup service loaded\n\n";

    // Default options
    $uploadsDir = realpath(ROOT_DIR . '/storage/uploads');
    $dryRun = true;
    $maxAgeDays = 30;
    $cleanTemp = true;

    // Parse command line arguments
    $options = [];
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--')) {
            $parts = explode('=', $arg, 2);
            $key = substr($parts[0], 2);
            $value = $parts[1] ?? true;
            $options[$key] = $value;
        }
    }

    // Override defaults with CLI arguments
    if (isset($options['delete'])) {
        $dryRun = false;
    }
    if (isset($options['max-age'])) {
        $maxAgeDays = (int) $options['max-age'];
    }
    if (isset($options['no-temp'])) {
        $cleanTemp = false;
    }
    if (isset($options['dir'])) {
        $uploadsDir = realpath($options['dir']);
    }

    // Validate directory
    if (!$uploadsDir || !is_dir($uploadsDir)) {
        echo '❌ Error: Uploads directory not found: ' . ($uploadsDir ?? 'null') . "\n";
        exit(1);
    }

    echo "=== Uploads Cleanup ===\n";
    echo "Directory: {$uploadsDir}\n";
    echo 'Mode: ' . ($dryRun ? 'DRY RUN (no files deleted)' : 'LIVE DELETE') . "\n";
    echo "Max age: {$maxAgeDays} days\n";
    echo 'Clean temp: ' . ($cleanTemp ? 'Yes' : 'No') . "\n";
    echo "Excluded files: .htaccess, .gitkeep, .gitignore, index.html, index.php\n\n";

    // Show stats first
    echo "📊 Current Statistics:\n";
    echo str_repeat('-', 40) . "\n";

    $stats = $cleanupService->getUploadStats($uploadsDir);
    echo sprintf("Total files: %d\n", $stats['total_files']);
    echo sprintf("Total size: %s\n", formatBytes($stats['total_size']));
    echo sprintf(
        "Temp files: %d (%s)\n",
        $stats['temp_files'],
        formatBytes($stats['temp_size']),
    );
    echo sprintf(
        "Orphan candidates: %d (%s)\n\n",
        $stats['orphan_candidates'],
        formatBytes($stats['orphan_size']),
    );

    // Run cleanup
    $result = $cleanupService->cleanupOrphanFiles($uploadsDir, [
        'dry_run' => $dryRun,
        'max_age_days' => $maxAgeDays,
        'clean_temp_files' => $cleanTemp,
    ]);

    // Show results
    showResults($result, $dryRun);

    // Ask for confirmation if dry run found candidates
    if ($dryRun && $result->getCandidateCount() > 0) {
        echo "\n⚠️  WARNING: This will delete {$result->getCandidateCount()} files.\n";
        echo "Run with --delete to actually delete files.\n";
        echo "Example: php bin/cleanup-uploads.php --delete --max-age=30\n";
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

function showResults($result, bool $dryRun): void
{
    // Get result data - assuming CleanupResult has methods to get data
    $candidateCount = method_exists($result, 'getCandidateCount') ? $result->getCandidateCount() : 0;
    $deletedCount = method_exists($result, 'getDeletedCount') ? $result->getDeletedCount() : 0;
    $deletedSize = method_exists($result, 'getTotalSizeDeleted') ? $result->getTotalSizeDeleted() : 0;
    $failedCount = method_exists($result, 'getFailedCount') ? $result->getFailedCount() : 0;

    // Try to get array representation if method exists
    $resultArray = [];
    if (method_exists($result, 'toArray')) {
        $resultArray = $result->toArray();
    }

    if ($dryRun) {
        echo "📝 Files that would be deleted:\n";

        // Get actual delete candidates (not including too recent or other skips)
        $actualCandidates = [];
        $candidateSize = 0;

        if (isset($resultArray['candidates'])) {
            foreach ($resultArray['candidates'] as $candidate) {
                // Only count files that would actually be deleted
                // (not ones marked as skipped)
                if (!isset($candidate['action']) || $candidate['action'] !== 'skipped') {
                    $actualCandidates[] = $candidate;
                    $candidateSize += $candidate['size'] ?? 0;
                }
            }
        }

        echo sprintf("  Count: %d\n", count($actualCandidates));
        echo sprintf("  Size: %s\n", formatBytes($candidateSize));

        // Show sample files (only actual deletion candidates)
        if (!empty($actualCandidates)) {
            echo "\n  Sample files (oldest 5):\n";

            // Sort by modified_at if available, otherwise by filename
            usort($actualCandidates, function ($a, $b) {
                $aTime = $a['modified_at'] ?? 0;
                $bTime = $b['modified_at'] ?? 0;

                if ($aTime && $bTime) {
                    return $aTime <=> $bTime;
                }

                // Fallback to filename if no timestamps
                return basename($a['path'] ?? '') <=> basename($b['path'] ?? '');
            });

            $count = 0;
            foreach ($actualCandidates as $file) {
                if ($count >= 5) {
                    break;
                }

                // Calculate age in days
                $age = 'unknown';
                if (isset($file['modified_at']) && $file['modified_at'] > 0) {
                    $ageDays = (time() - $file['modified_at']) / 86400;
                    $age = round($ageDays, 1) . ' days';
                }

                $filename = basename($file['path']);
                echo sprintf(
                    "    - %s (%s, %s old)\n",
                    $filename,
                    formatBytes($file['size'] ?? 0),
                    $age,
                );
                $count++;
            }

            if (count($actualCandidates) > 5) {
                echo sprintf("    ... and %d more\n", count($actualCandidates) - 5);
            }
        }
    } else {
        echo "✅ Cleanup completed:\n";
        echo sprintf("  Deleted: %d files\n", $deletedCount);
        echo sprintf("  Freed: %s\n", formatBytes($deletedSize));
        echo sprintf("  Failed: %d files\n", $failedCount);

        // Show failed files if any
        if (isset($resultArray['failed']) && !empty($resultArray['failed'])) {
            echo "\n  Failed deletions:\n";
            foreach ($resultArray['failed'] as $file) {
                echo sprintf("    - %s: %s\n", basename($file['path']), $file['error'] ?? 'Unknown error');
            }
        }
    }
}