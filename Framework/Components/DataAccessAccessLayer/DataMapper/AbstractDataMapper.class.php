<?php

declare(strict_types=1);

abstract class AbstractDataMapper implements DataMapperInterface
{
    protected DatabaseConnexionInterface $_con;
    protected PDOStatement $_query;

    public function __construct(DataMapperEnvironmentConfig $env)
    {
        $this->_con = $this->connection($env);
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

    public function getConnexion() : DatabaseConnexionInterface
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

    private function connection(DataMapperEnvironmentConfig $env) : DatabaseConnexionInterface
    {
        $credentials = $env->getCredentials();
        $app = App::getInstance();
        $app->singleton(DatabaseConnexionInterface::class, PDOConnexion::class, $credentials);
        return $app->get(DatabaseConnexionInterface::class);
    }
}