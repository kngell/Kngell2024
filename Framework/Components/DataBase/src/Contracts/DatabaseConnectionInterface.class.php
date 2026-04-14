<?php

declare(strict_types=1);

interface DatabaseConnectionInterface
{
    /**
     * DataBase open
     * -----------------------------------------------.
     *
     * @return PDO
     */
    public function open(): PDO;

    /**
     * Data Base close
     * ------------------------------------------------.
     *
     * @return void
     */
    public function close(): void;

    /**
     * beginTransaction
     * ------------------------------------------------.
     *
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * Commit
     * ------------------------------------------------.
     *
     * @return bool
     */
    public function commit(): bool;

    /**
     * Rollback.
     * ------------------------------------------------.
     *
     * @return bool
     */
    public function rollback(): bool;

    public function lastInsertId(string|null $name = null): string|false;

    public function quote(string $value): string;

    public function getAttribute(int $attribute): mixed;

    public function getDriverName(): string;

    public function getServerVersion(): string;

    public function isMariaDB(): bool;

    public function getDatabaseVersion(): float;
}
