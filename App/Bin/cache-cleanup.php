<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

// Include CLI bootstrap
require_once __DIR__ . '/cli-bootstrap.php';

// Parse arguments
$options = getopt('efvh', ['expired-only', 'force', 'verbose', 'help']);
$expiredOnly = isset($options['e']) || isset($options['expired-only']);
$force = isset($options['f']) || isset($options['force']);
$verbose = isset($options['v']) || isset($options['verbose']);
$help = isset($options['h']) || isset($options['help']);

if ($help) {
    echo "Cache Cleanup Script\n";
    echo "===================\n\n";
    echo "Usage: php cache-cleanup.php [options]\n\n";
    echo "Options:\n";
    echo "  -e, --expired-only  Clean only expired cache items\n";
    echo "  -f, --force         Force cleanup (ignore cache)\n";
    echo "  -v, --verbose       Show detailed output\n";
    echo "  -h, --help          Show this help\n\n";
    echo "Examples:\n";
    echo "  php cache-cleanup.php                    # Full cleanup\n";
    echo "  php cache-cleanup.php --expired-only -v  # Verbose expired-only\n";
    exit(0);
}

try {
    if ($verbose) {
        echo "🔧 Starting cache cleanup...\n";
        echo '   Mode: ' . ($expiredOnly ? 'expired-only' : 'full') . "\n";
        echo '   Force: ' . ($force ? 'yes' : 'no') . "\n";
    }

    // Bootstrap the full application
    $app = bootstrapCliApp();
    $container = $app; // App extends Container

    if ($verbose) {
        echo "📦 Getting cache services...\n";
    }

    // Get cache instance (from App)
    $cache = $container->resolve(CacheInterface::class);
    $logger = $container->resolve(LoggerInterface::class);

    // Try to get CacheGarbageCollector from container
    if ($container->has(CacheGarbageCollector::class)) {
        $garbageCollector = $container->resolve(CacheGarbageCollector::class);
    } elseif (class_exists(CacheGarbageCollector::class)) {
        // Create manually if not in container
        $garbageCollector = new CacheGarbageCollector($cache, $logger);
    } else {
        // Fallback: direct cache cleanup
        $garbageCollector = null;
    }

    $startTime = microtime(true);

    if ($expiredOnly) {
        if ($verbose) {
            echo "🧹 Cleaning expired items...\n";
        }

        if ($garbageCollector && method_exists($garbageCollector, 'collectExpiredOnly')) {
            $result = $garbageCollector->collectExpiredOnly();
            echo sprintf(
                "Removed %d expired cache items (scanned %d keys)\n",
                $result['removed'] ?? 0,
                $result['scanned'] ?? 0,
            );
        } else {
            // Manual expired cleanup
            $removed = cleanupExpiredManually($cache, $verbose);
            echo "Removed $removed expired cache items\n";
        }
    } else {
        if ($verbose) {
            echo "🗑️  Running full garbage collection...\n";
        }

        if ($garbageCollector && method_exists($garbageCollector, 'collect')) {
            $result = $garbageCollector->collect();
            echo sprintf(
                "Collected %d items\n",
                $result['collected'] ?? 0,
            );
        } else {
            // Fallback to expired cleanup
            $removed = cleanupExpiredManually($cache, $verbose);
            echo "Cleaned $removed items\n";
        }
    }

    $duration = round(microtime(true) - $startTime, 2);

    if ($verbose) {
        echo sprintf("⏱️  Duration: %s seconds\n", $duration);
        echo "✨ Cleanup completed!\n";
    }

    exit(0);
} catch (Throwable $e) {
    handleCliError($e);
}

/**
 * Manual expired cleanup if garbage collector is not available.
 */
function cleanupExpiredManually(CacheInterface $cache, bool $verbose = false): int
{
    $removed = 0;

    // Get all cache keys
    if (method_exists($cache, 'getKeys')) {
        $keys = $cache->getKeys();

        if ($verbose) {
            echo '   Scanning ' . count($keys) . " keys...\n";
        }

        foreach ($keys as $key) {
            // Check if item exists (expired items return null)
            $value = $cache->get($key);
            if ($value === null) {
                $cache->delete($key);
                $removed++;
            }
        }
    } elseif (method_exists($cache, 'collectGarbage')) {
        // Use built-in garbage collection
        $removed = $cache->collectGarbage();
    }

    return $removed;
}