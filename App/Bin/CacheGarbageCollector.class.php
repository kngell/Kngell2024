<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class CacheGarbageCollector
{
    public function __construct(
        private CacheInterface $cache,
        private LoggerInterface $logger,
        private string $cachePrefix = '',
    ) {
    }

    public function collect(): array
    {
        $results = [
            'started' => time(),
            'collected' => 0,
            'errors' => [],
        ];

        try {
            if (!method_exists($this->cache, 'collectGarbage')) {
                $this->logger->warning('Cache driver does not support garbage collection');
                return $results;
            }

            $collected = $this->cache->collectGarbage();
            $results['collected'] = $collected;

            $this->logger->info('Cache garbage collection completed', [
                'collected' => $collected,
                'duration' => time() - $results['started'],
            ]);
        } catch (Throwable $e) {
            $results['errors'][] = $e->getMessage();
            $this->logger->error('Cache garbage collection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        $results['finished'] = time();
        return $results;
    }

    public function collectExpiredOnly(): array
    {
        // More targeted: only collect expired items
        $results = [
            'started' => time(),
            'scanned' => 0,
            'removed' => 0,
            'errors' => [],
        ];

        try {
            // Get all cache keys with your prefix
            $keys = $this->cache->getKeys($this->cachePrefix . '*');
            $results['scanned'] = count($keys);

            foreach ($keys as $key) {
                if (!$this->cache->exists($key)) {
                    // Auto-expired or already removed
                    $results['removed']++;
                    $this->cache->delete($key);
                }
            }

            $this->logger->info('Expired cache items cleaned', $results);
        } catch (Throwable $e) {
            $results['errors'][] = $e->getMessage();
            $this->logger->error('Expired cache cleanup failed', [
                'error' => $e->getMessage(),
            ]);
        }

        $results['finished'] = time();
        return $results;
    }
}