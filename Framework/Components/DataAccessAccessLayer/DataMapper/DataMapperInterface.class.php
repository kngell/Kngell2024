<?php

declare(strict_types=1);

interface DataMapperInterface
{
    public function getQueryStatement(): null|PDOStatement;

    public function getExecutionStatus(): bool;

    public function persist(string $sql = '', array $parameters = [], bool $isSearch = false): self;

    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollback(): bool;

    public function getConnexion(): DatabaseConnectionInterface;

    public function shouldExecute(): bool;

    public function lastInsertId(?string $name = null): string|false;

    public function getQueryString(): string;

    public function getQueryParameters(): array;

    public function reset(): self;

    public function fullReset(): self;

    public function quote(string $value): string;

    public function getAttribute(int $attribute): mixed;

    public function getDriverName(): string;

    public function getServerVersion(): string;

    public function isMariaDB(): bool;

    public function getDatabaseVersion(): float;
}