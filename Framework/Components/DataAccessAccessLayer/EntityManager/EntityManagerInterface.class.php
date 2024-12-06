<?php

declare(strict_types=1);

interface EntityManagerInterface
{
    public function createQueryBuilder() : QueryBuilder;

    public function setEntity(Entity $entity): self;

    public function table() : string;

    public function getConnection() : DatabaseConnexionInterface;

    public function getTableAliasHelper(): TablesAliasHelper;

    public function assign(array $data) : self;

    public function getEntity(): Entity;

    public function getEntityProperties() : array;

    public function isEntityKeyInitialized() : bool;

    public function getQueryExpr(): MainQuery;

    public function setQueryExpr(MainQuery $queryExpr): self;

    public function persist() : self;

    public function getRepository(Entity|string|null $entityName = null) : array|RepositoryInterface;

    public function getEntityKeyField(): string|bool;

    public function getEntityKeyValue() : mixed;

    public function getResults(array $options = [], string|null $repositoryMethod = null) : QueryResult;
}
