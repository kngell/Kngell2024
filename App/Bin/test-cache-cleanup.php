<?php

declare(strict_types=1);

defined('ROOT_DIR') or define('ROOT_DIR', realpath(dirname(__DIR__)));

require_once __DIR__ . '/../../vendor/autoload.php';

echo "🧪 Testing Cache Cleanup\n";
echo "=======================\n\n";

try {
    // Bootstrap
    $app = new App();
    $container = $app->boot();
    $cache = $container->get(CacheInterface::class);
    $garbageCollector = $container->get(CacheGarbageCollector::class);

    // 1. Add some test cache items
    echo "1. Adding test cache items...\n";

    // Add items that will expire soon
    $testKeys = [];
    for ($i = 1; $i <= 5; $i++) {
        $key = "test_item_$i";
        $cache->set($key, "value_$i", 2); // 2 second TTL
        $testKeys[] = $key;
        echo "   Added: $key (2s TTL)\n";
    }

    // Add permanent items
    $cache->set('test_permanent_1', 'permanent_value_1', 3600);
    $cache->set('test_permanent_2', 'permanent_value_2', 3600);
    echo "   Added: test_permanent_1, test_permanent_2 (1h TTL)\n\n";

    // 2. Wait for some items to expire
    echo "2. Waiting for items to expire...\n";
    sleep(3);
    echo "   Waited 3 seconds\n\n";

    // 3. Check what's in cache before cleanup
    echo "3. Cache before cleanup:\n";
    foreach ($testKeys as $key) {
        $exists = $cache->exists($key) ? 'YES' : 'NO (expired)';
        echo "   $key: $exists\n";
    }
    echo '   test_permanent_1: ' . ($cache->exists('test_permanent_1') ? 'YES' : 'NO') . "\n";
    echo '   test_permanent_2: ' . ($cache->exists('test_permanent_2') ? 'YES' : 'NO') . "\n\n";

    // 4. Run expired-only cleanup
    echo "4. Running expired-only cleanup...\n";
    $result = $garbageCollector->collectExpiredOnly();
    echo sprintf(
        "   Result: scanned %d keys, removed %d expired items\n\n",
        $result['scanned'] ?? 0,
        $result['removed'] ?? 0,
    );

    // 5. Check cache after cleanup
    echo "5. Cache after cleanup:\n";
    foreach ($testKeys as $key) {
        $exists = $cache->exists($key) ? 'YES' : 'NO';
        echo "   $key: $exists\n";
    }
    echo '   test_permanent_1: ' . ($cache->exists('test_permanent_1') ? 'YES' : 'NO') . "\n";
    echo '   test_permanent_2: ' . ($cache->exists('test_permanent_2') ? 'YES' : 'NO') . "\n\n";

    // 6. Clean up test items
    echo "6. Cleaning up test items...\n";
    foreach ($testKeys as $key) {
        $cache->delete($key);
    }
    $cache->delete('test_permanent_1');
    $cache->delete('test_permanent_2');
    echo "   Test items removed\n\n";

    echo "✅ Test completed!\n";
} catch (Throwable $e) {
    echo '❌ Test failed: ' . $e->getMessage() . "\n";
    exit(1);
}