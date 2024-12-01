<?php

declare(strict_types=1);

class EntityManager implements EntityManagerInterface
{
    private DatabaseConnexionInterface $conn;
    private TablesAliasHelper $tableAliasHelper;
    private Entity $entity;
    private $repositories = [];

    public function __construct(DatabaseConnexionInterface $conn, TablesAliasHelper $tableAliasHelper)
    {
        $this->conn = $conn;
        $this->tableAliasHelper = $tableAliasHelper;
    }

    public function beginTransaction()
    {
        $this->conn->beginTransaction();
    }

    public function commit()
    {
        $this->conn->commit();
    }

    public function rollback()
    {
        $this->conn->rollback();
    }

    public function getConnection() : DatabaseConnexionInterface
    {
        return $this->conn;
    }

    public function createQueryBuilder() : QueryBuilder
    {
        return new QueryBuilder($this);
    }

    public function getRepository($entityName)
    {
        $entityName = ltrim($entityName, '\\');

        if (isset($this->repositories[$entityName])) {
            return $this->repositories[$entityName];
        }

        // $metadata = $this->getClassMetadata($entityName);
        // $repositoryClassName = $metadata->customRepositoryClassName;

        // if ($repositoryClassName === null) {
        //     $repositoryClassName = $this->config->getDefaultRepositoryClassName();
        // }

        // $repository = new $repositoryClassName($this, $metadata);

        // $this->repositories[$entityName] = $repository;

        // return $repository;
    }

    /**
     * @param mixed $entityName
     * @param mixed $identifier
     * @return object
     */
    public function find($entityName, $identifier) : object
    {
        return $this->getRepository($entityName)->find($identifier);
    }

    public static function create(DatabaseConnexionInterface $conn, TablesAliasHelper $tblh)
    {
        return new self($conn, $tblh);
    }

    /**
     * Set the value of entity.
     *
     * @param Entity $entity
     *
     * @return self
     */
    public function setEntity(Entity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function table() : string
    {
        return $this->entity->table();
    }

    /**
     * Get the value of tableAliasHelper.
     *
     * @return TablesAliasHelper
     */
    public function getTableAliasHelper(): TablesAliasHelper
    {
        return $this->tableAliasHelper;
    }
}