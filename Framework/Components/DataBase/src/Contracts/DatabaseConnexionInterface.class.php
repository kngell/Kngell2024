<?php

declare(strict_types=1);

interface DatabaseConnexionInterface
{
    /**
     * DataBase open
     * -----------------------------------------------.
     * @return PDO
     */
    public function open(): PDO;

    /**
     * Data Base close
     * ------------------------------------------------.
     * @return void
     */
    public function close():void;

    /**
     * beginTransaction
     * ------------------------------------------------.
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * Commit
     * ------------------------------------------------.
     * @return bool
     */
    public function commit() :  bool;

    /**
     * Rollback.
     * ------------------------------------------------.
     * @return bool
     */
    public function rollback() : bool;
}