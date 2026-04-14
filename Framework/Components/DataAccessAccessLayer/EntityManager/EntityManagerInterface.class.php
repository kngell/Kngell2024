<?php

declare(strict_types=1);

interface EntityManagerInterface
{
    public function createQueryBuilder(): QueryBuilder;

    public function setEntity(Entity|array|CollectionInterface $entity): self;

    public function table(): string;

    public function getConnection(): DatabaseConnectionInterface;

    public function getTableAliasHelper(): TablesAliasHelper;

    public function assign(array $data): self;

    public function getEntity(): Entity|CollectionInterface;

    public function getEntityProperties(): array;

    public function isEntityKeyInitialized(): bool;

    public function setQueryBuilder(?AbstractQueryBuilder $queryBuilder): self;

    public function persist(): self;

    public function getRepository(Entity|string|null $entityName = null): array|RepositoryInterface|ProductRegionalPriceRepositoryInterface;

    public function getEntityKeyField(): string|bool;

    public function getEntityKeyValue(): mixed;

    public function getQueryResult(): QueryResult;

    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollback(): bool;

    public function getTableAlias(): array;

    public function getNormalizer(): TypeNormalizerInterface;

    public function hasData(): bool;

    public function getEntityData(): array;

    public function getDirtyData(): array;

    public function getLastOperationId(): null|int;

    public function trackChangesWithData(array $data): void;

    public function getEntityContext(): Entity;

    public function reset(): self;

    public function isCollectionOfEntities(): bool;

    public function quote(string $value): string;

    public function getAttribute(int $attribute): mixed;

    public function getDriverName(): string;

    public function getServerVersion(): string;

    public function isMariaDB(): bool;

    public function getDatabaseVersion(): float;

    public function getSqlTypeHandler(): SqlTypeHandlerFactory;
}