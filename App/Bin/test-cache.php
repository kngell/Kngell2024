<?php

// /home/kngell/projects/kngell-ecom/App/Bin/test-cache.php

declare(strict_types=1);

// Include CLI bootstrap
require_once __DIR__ . '/cli-bootstrap.php';

echo "🧪 Testing Cache System\n";
echo "======================\n\n";

try {
    echo "1. Bootstrapping application...\n";
    $app = bootstrapCliApp();
    $container = $app;

    echo "✅ Application bootstrapped\n\n";

    echo "2. Testing cache service...\n";
    $cache = $container->resolve(CacheInterface::class);

    // Test 1: Set and get
    $testKey = 'cli_test_' . time();
    $testValue = 'test_value_' . rand(1000, 9999);

    echo "   Setting test key: $testKey\n";
    $cache->set($testKey, $testValue, 10); // 10 second TTL

    echo "   Retrieving test key...\n";
    $retrieved = $cache->get($testKey);

    if ($retrieved === $testValue) {
        echo "   ✅ Cache set/get works: $retrieved\n";
    } else {
        echo "   ❌ Cache set/get failed\n";
    }

    // Test 2: Check if key exists
    if ($cache->exists($testKey)) {
        echo "   ✅ Cache exists() works\n";
    } else {
        echo "   ❌ Cache exists() failed\n";
    }

    // Test 3: Delete
    $cache->delete($testKey);
    if (!$cache->exists($testKey)) {
        echo "   ✅ Cache delete() works\n";
    } else {
        echo "   ❌ Cache delete() failed\n";
    }

    // Test 4: Try to get garbage collector
    echo "\n3. Testing garbage collector...\n";
    if ($container->has(CacheGarbageCollector::class)) {
        $garbageCollector = $container->resolve(CacheGarbageCollector::class);
        echo "   ✅ CacheGarbageCollector found in container\n";

        // Test methods
        if (method_exists($garbageCollector, 'collect')) {
            echo "   Has collect() method: YES\n";
        }
        if (method_exists($garbageCollector, 'collectExpiredOnly')) {
            echo "   Has collectExpiredOnly() method: YES\n";
        }
    } else {
        echo "   ⚠️  CacheGarbageCollector not in container\n";
    }

    echo "\n🎉 All tests completed!\n";
} catch (Throwable $e) {
    echo '❌ Test failed: ' . $e->getMessage() . "\n";
    exit(1);
}