<?php

declare(strict_types=1);

abstract class AbstractDataMapper implements DataMapperInterface
{
    protected DatabaseConnectionInterface $_con;
    protected PDOStatement $_query;

    public function __construct(DatabaseConnectionInterface $_con)
    {
        $this->_con = $_con;
    }

    public function beginTransaction() : bool
    {
        return $this->_con->beginTransaction();
    }

    public function commit() :  bool
    {
        return $this->_con->commit();
    }

    public function rollback() : bool
    {
        return $this->_con->rollback();
    }

    public function getConnexion() : DatabaseConnectionInterface
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
}
