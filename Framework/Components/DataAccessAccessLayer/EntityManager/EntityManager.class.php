<?php

declare(strict_types=1);

class EntityManager implements EntityManagerInterface
{
    private DataMapperInterface $mapper;
    private TablesAliasHelper $tableAliasHelper;
    private Entity $entity;
    private ReflectionObject $reflector;
    private $repositories = [];
    private MainQuery $queryExpr;
    private string $entityFieldId;

    public function __construct(DataMapperInterface $mapper, TablesAliasHelper $tableAliasHelper)
    {
        $this->mapper = $mapper;
        $this->tableAliasHelper = $tableAliasHelper;
    }

    public function beginTransaction()
    {
        $this->mapper->beginTransaction();
    }

    public function commit()
    {
        $this->mapper->commit();
    }

    public function rollback()
    {
        $this->mapper->rollback();
    }

    public function getConnection() : DatabaseConnexionInterface
    {
        return $this->mapper->getConnexion();
    }

    public function createQueryBuilder() : QueryBuilder
    {
        return new QueryBuilder($this);
    }

    public function getRepository(Entity|string|null $entityName = null) : array|RepositoryInterface
    {
        if (null !== $entityName) {
            if ($entityName instanceof Entity) {
                $entityName = $entityName::class;
                $this->entity = $entityName;
            }

            if (isset($this->repositories[$entityName])) {
                return $this->repositories[$entityName];
            }
            $repositoryClassName = $entityName . 'Repository';
            if (class_exists($repositoryClassName)) {
                $this->repositories[$entityName] = new $repositoryClassName($this);
                return  $this->repositories[$entityName];
            }
        }
        return new Repository($this);
    }

    public function persist() : self
    {
        $sql = $this->queryExpr->getQuery();
        $parameters = $this->queryExpr->getParameters();
        $bindArray = $this->queryExpr->getBindArr();
        $mapper = $this->mapper->persist($sql, $parameters, false);
        return $this;
    }

    public function getResults() : QueryResult
    {
        return new QueryResult($this->mapper, $this->entity);
    }

    public function assign(array $data) : self
    {
        /** @var ReflectionProperty[] */
        $attrs = $this->reflector->getProperties(ReflectionProperty::IS_PRIVATE);

        foreach ($data as $key => $prop) {
            $ok = array_filter($attrs, function ($attr) use ($key) {
                return StringUtils::camelCase($key) === $attr->getName();
            });
            if ($ok) {
                /** @var ReflectionProperty */
                $property = ArrayUtils::first($ok);
                $property->setAccessible(true);
                $property->setValue($this->entity, $prop);
            }
        }
        return $this;
    }

    public function getEntityKeyField(): string|bool
    {
        if (isset($this->entityFieldId)) {
            return $this->entityFieldId;
        }
        return $this->entity->getEntityKeyField();
    }

    public function getEntityKeyValue() : mixed
    {
        $keyField = StringUtils::studlyCaps($this->getEntityKeyField());
        $method = 'get' . ucfirst($keyField);
        return $this->reflector->getMethod($method)->invoke($this->entity, $method);
    }

    public function isEntityKeyInitialized() : bool
    {
        $fieldId = $this->entityFieldId ?? $this->getEntityKeyField();
        $properties = $this->reflector->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $prop = StringUtils::StudlyCapsToUnderscore($property->getName());
            if ($prop === $fieldId && $property->isInitialized($this->entity)) {
                return true;
            }
        }
        return false;
    }

    public function getEntityProperties() : array
    {
        $properties = [];
        $all = $this->reflector->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PRIVATE);
        foreach ($all as $property) {
            if ($property->isInitialized($this->entity)) {
                $field = StringUtils::StudlyCapsToUnderscore($property->getName());
                if ($property->getType()->getName() === 'DateTimeInterface') {
                    $properties[$field] = $property->getValue($this->entity)->format('Y-m-d H:i:s');
                } else {
                    $properties[$field] = $property->getValue($this->entity);
                }
            }
        }
        return $properties;
    }

    public static function create(DataMapperInterface $mapper, TablesAliasHelper $tblh)
    {
        return new self($mapper, $tblh);
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
        $this->reflector = new ReflectionObject($this->entity);
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

    /**
     * Get the value of entity.
     *
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * Get the value of queryExpr.
     *
     * @return MainQuery
     */
    public function getQueryExpr(): MainQuery
    {
        return $this->queryExpr;
    }

    /**
     * Set the value of queryExpr.
     *
     * @param MainQuery $queryExpr
     *
     * @return self
     */
    public function setQueryExpr(MainQuery $queryExpr): self
    {
        $this->queryExpr = $queryExpr;

        return $this;
    }
}
