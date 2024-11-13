<?php

declare(strict_types=1);

class DataAccessLayerManager
{
    private string $tableSchema;
    private string $tableSchemaID;
    private array $options;
    private ContainerInterface $container;
    private DataMapperEnvironmentConfig $dataMapperEnvConfig;
    private Entity $entity;

    /**
     * Main contructor
     *=====================================================================.
     * @param DataMapperEnvironmentConfig $datamapperEnvConfig
     * @param string $tableSchema
     * @param string $tableSchemaID
     */
    public function __construct(DataMapperEnvironmentConfig $dataMapperEnvConfig, string $tableSchema, string $tableSchemaID, ?array $options, Entity $entity)
    {
        $this->tableSchema = $tableSchema;
        $this->tableSchemaID = $tableSchemaID;
        $this->options = $options;
        $this->entity = $entity;
        $this->dataMapperEnvConfig = $dataMapperEnvConfig;
    }

    /**
     * Initializind ORM DataBase Management
     * =====================================================================.
     * @return void
     */
    public function initialize()
    {
        return $this->container->make(EntityManagerFactory::class, [
            'tableSchema' => $this->tableSchema,
            'tableSchamaID' => $this->tableSchemaID,
            'options' => $this->options,
            'dataMapperEnvConfig' => $this->dataMapperEnvConfig,

        ])->create();
        // $emFactory->getDataMapper()->setCredentials($this->dataMapperEnvConfig->getCredentials('mysql'));
        // return $emFactory->create($this->tableSchema, $this->tableSchameID, $this->options);
    }
}
