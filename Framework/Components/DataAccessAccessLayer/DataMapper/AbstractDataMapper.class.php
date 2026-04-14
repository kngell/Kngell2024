<?php

declare(strict_types=1);

abstract class AbstractDataMapper implements DataMapperInterface
{
    protected DatabaseConnectionInterface $_con;
    protected PDOStatement $_query;
    protected bool $executionStatus = false;
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

    public function quote(string $value): string
    {
        return $this->_con->quote($value);
    }

    public function getConnexion(): DatabaseConnectionInterface
    {
        return $this->_con;
    }

    /**
     * Get the value of _query.
     *
     * @return null|PDOStatement
     */
    public function getQueryStatement(): null|PDOStatement
    {
        return isset($this->_query) ? $this->_query : null;
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
        return isset($this->_query) ? $this->_query->queryString : '';
    }

    public function getQueryParameters(): array
    {
        return $this->parameters;
    }

    public function reset(): self
    {
        $this->parameters = [];

        if (isset($this->_query)) {
            unset($this->_query);
        }
        return $this;
    }

    public function fullReset(): self
    {
        $this->executionStatus = false;
        $this->parameters = [];
        if (isset($this->_query)) {
            unset($this->_query);
        }
        return $this;
    }

    public function getAttribute(int $attribute): mixed
    {
        return $this->_con->getAttribute($attribute);
    }

    public function getDriverName(): string
    {
        return $this->_con->getDriverName();
    }

    public function getServerVersion(): string
    {
        return $this->_con->getServerVersion();
    }

    public function isMariaDB(): bool
    {
        return $this->_con->isMariaDB();
    }

    public function getDatabaseVersion(): float
    {
        return $this->_con->getDatabaseVersion();
    }
}