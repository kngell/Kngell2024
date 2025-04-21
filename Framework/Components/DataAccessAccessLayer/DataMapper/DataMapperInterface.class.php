<?php

declare(strict_types=1);

interface DataMapperInterface
{
    public function getQueryStatement(): PDOStatement;

    public function getQueryResult(): bool;

    public function persist(string $sql = '', array $parameters = [], bool $isSearch = false) : self;

    public function hasResults() : bool;

    public function beginTransaction() : bool;

    public function commit() :  bool;

    public function rollback() : bool;

    public function getConnexion() : DatabaseConnexionInterface;
}