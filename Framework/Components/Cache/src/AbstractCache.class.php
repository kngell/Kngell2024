<?php

declare(strict_types=1);

abstract class AbstractCache implements CacheInterface
{
    /** @var string regular expression - ensure cache name is of correct values */
    const PATTERN_ENTRYIDENTIFIER = '/^[a-zA-Z0-9_\.]{1,64}$/';
    /** @var object|null */
    protected ?object $storage;
    /** @var string|null */
    protected ?string $cacheIdentifier;
    /** @var array */
    protected array $options = [];

    public function __construct(?string $cacheIdentifier, ?CacheStorageInterface $storage, array $options = [])
    {
        $this->cacheIdentifier = $cacheIdentifier;
        if (!empty($storage) && $storage != null) {
            $this->storage = $storage;
        }
        if ($options) {
            $this->options = $options;
        }
    }

    /**
     * Check cache identifier matches the regular expression if not throw an
     * exception. cache name can only contains letter, number, underscore and
     * should have a minimum or 1 and a maximum of 64 characters. No special
     * characters are allowed.
     *
     * @param string $key
     * @return bool
     */
    protected function isCacheEntryIdentifiervalid(string $key): bool
    {
        return preg_match(self::PATTERN_ENTRYIDENTIFIER, $key) === 1;
    }

    /**
     * throw a n cacheInvalidArgumentException is the cache key is invalid.
     *
     * @param string $key
     * @return void
     * @throws CacheInvalidArgumentException
     */
    protected function ensureCacheEntryIdentifierIsvalid(string $key): void
    {
        if ($this->isCacheEntryIdentifiervalid($key) === false) {
            throw new CacheInvalidArgumentException('"' . $key . '" is not a valid cache key.', 0);
        }
    }
}