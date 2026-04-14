<?php

declare(strict_types=1);

interface EntityCacheKeyGeneratorInterface
{
    public function getCacheKey(Entity $entity): string;

    public function getCacheIdentifier(Entity $entity): string;

    public function getCacheKeyWithType(Entity $entity): string;

    public function getCacheKeyFromIdentifier(string $entityClass, string $identifier): string;

    public function extractIdentifierFromKey(string $cacheKey, string $entityClass): ?string;

    public function normalizeClassName(string $className): string;

    public function getEntityPrefix(string $entityClass): string;
}