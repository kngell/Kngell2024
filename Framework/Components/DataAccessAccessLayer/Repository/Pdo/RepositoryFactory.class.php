<?php

declare(strict_types=1);

class RepositoryFactory
{
    protected string $tableSchema;
    protected string $tableSchemaID;
    protected string $crudIdentifier;
    protected ContainerInterface $container;
    protected Entity $entity;

    /**
     * Main constructor
     * ==================================================================.
     */
    public function __construct(string $crudIdentifier, string $tableSchema, string $tableSchemaID, Entity $entity)
    {
        $this->crudIdentifier = $crudIdentifier;
        $this->tableSchema = $tableSchema;
        $this->tableSchemaID = $tableSchemaID;
        $this->entity = $entity;
    }

    /**
     * Create Data Repository
     *==================================================================.
     * @param string $datarepositoryString
     * @return RepositoryInterface
     */
    public function create() : RepositoryInterface
    {
        $repositoryObject = $this->container->make(RepositoryInterface::class, [
            'em' => $this->initializeDataAccessManager(),
        ]);
        if (!$repositoryObject instanceof RepositoryInterface) {
            throw new BaseUnexpectedValueException(get_class($repositoryObject) . ' is not a valid repository Object!');
        }

        return $repositoryObject;
    }

    public function initializeDataAccessManager()
    {
        return $this->container->make(DataAccessLayerManager::class, [
            'dataMapperEnvConfig' => $this->container->make(DataMapperEnvironmentConfig::class, [
                'credentials' => YamlFile::get('database'),
            ]),
            'tableSchema' => $this->tableSchema,
            'tableSchemaID' => $this->tableSchemaID,
            'entity' => $this->entity,
            'options' => [],
        ])->initialize();
    }
}
