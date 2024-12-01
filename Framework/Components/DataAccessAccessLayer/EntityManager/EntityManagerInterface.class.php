<?php

declare(strict_types=1);

interface EntityManagerInterface
{
    public function createQueryBuilder() : QueryBuilder;

    public function setEntity(Entity $entity): self;

    public function table() : string;

    public function getConnection() : DatabaseConnexionInterface;

    public function getTableAliasHelper(): TablesAliasHelper;
}