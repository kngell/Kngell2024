<?php

declare(strict_types=1);

abstract class AbstractDataMapper implements DataMapperInterface
{
    protected DatabaseConnectionInterface $_con;
    protected PDOStatement $_query;
    protected bool $executionStatus;
    protected array $parameters = [];

    public function __construct(DatabaseConnectionInterface $_con)
    {
        $this->_con = $_con;
    }

    public function beginTransaction(): bool
    {
        return $this->_con->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->_con->commit();
    }

    public function rollback(): bool
    {
        return $this->_con->rollback();
    }

    public function getConnexion(): DatabaseConnectionInterface
    {
        return $this->_con;
    }

    /**
     * Get the value of _query.
     *
     * @return PDOStatement
     */
    public function getQueryStatement(): PDOStatement
    {
        return $this->_query;
    }

    public function lastInsertId(?string $name = null): string|false
    {
        return $this->_con->lastInsertId($name);
    }

    /**
     * @return bool
     */
    public function getExecutionStatus(): bool
    {
        return $this->executionStatus;
    }

    public function getQueryString(): string
    {
        return $this->_query->queryString;
    }

    public function getQueryParameters(): array
    {
        return $this->parameters;
    }
}