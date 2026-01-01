<?php

declare(strict_types=1);

trait CacheRememberTrait
{
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $cachedValue = $this->get($key);

        if ($cachedValue !== null) {
            return $cachedValue;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    public function rememberForever(string $key, callable $callback): mixed
    {
        return $this->remember($key, $callback, null);
    }

    public function rememberWithTags(string $key, callable $callback, ?int $ttl = null, array $tags = []): mixed
    {
        $cachedValue = $this->get($key);

        if ($cachedValue !== null) {
            return $cachedValue;
        }

        $value = $callback();
        $this->setWithTags($key, $value, $ttl, $tags);

        return $value;
    }

    public function rememberMany(array $keys, callable $callback, ?int $ttl = null): array
    {
        $results = [];
        $missingKeys = [];

        // Check cache for each key
        foreach ($keys as $key) {
            $cached = $this->get($key);
            if ($cached !== null) {
                $results[$key] = $cached;
            } else {
                $missingKeys[] = $key;
            }
        }

        // If all keys are cached, return early
        if (empty($missingKeys)) {
            return $results;
        }

        // Fetch missing keys
        $newValues = $callback($missingKeys);

        if (!is_array($newValues)) {
            throw new CacheException('Callback must return an array');
        }

        // Store new values and add to results
        foreach ($newValues as $key => $value) {
            $this->set($key, $value, $ttl);
            $results[$key] = $value;
        }

        return $results;
    }
}