<?php

declare(strict_types=1);

interface EntityFactoryInterface
{
    public function create(
        string $entityClass,
        array $tableAlias = [],
        array $tableMap = [],
    ): Entity;

    public function createFromDatabase(
        string $entityClass,
        array $data,
        array $tableAlias = [],
        array $tableMap = [],
    ): Entity;

    public function createFromClient(
        string $entityClass,
        array $data,
        array $tableAlias = [],
        array $tableMap = [],
    ): Entity;

    public function createWithRelationships(
        string $entityClass,
        array $data,
        array $relationships,
        array $tableAlias = [],
        array $tableMap = [],
    ): Entity;

    public function getPrimaryKeyField(string $entityClass): string;

    public function getNormalizer(): TypeNormalizerInterface;

    public function getChangeTracker(): ChangeTrackerInterface;

    public function getDependencies(): EntityDependenciesFactoryInterface;

    public function getRelationManager(): EntityRelationManagerInterface;
}