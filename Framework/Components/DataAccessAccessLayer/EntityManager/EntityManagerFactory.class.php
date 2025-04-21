<?php

declare(strict_types=1);

class EntityManagerFactory
{
    private ContainerInterface $container;
    private DataMapperInterface $dataMapper;
    private QueryBuilderInterface $queryBuilder;
    private DataMapperEnvironmentConfig $dataMapperEnvConfig;
    private string $tableSchema;
    private string $tableSchemaID;
    private array $options;

    /**
     * Main constructor
     * =====================================================================.
     *
     * @param DataMapperInterface $datamapper
     * @param QueryBuilderInterface $querybuilder
     */
    public function __construct(DataMapperEnvironmentConfig $dataMapperEnvConfig, string $tableSchema, string $tableSchamaID, ?array $options)
    {
        $this->tableSchema = $tableSchema;
        $this->tableSchemaID = $tableSchamaID;
        $this->options = $options;
        $this->dataMapperEnvConfig = $dataMapperEnvConfig;
    }

    /**
     * Create EntityManager
     * =====================================================================.
     *
     * @param string $tableSchma
     * @param string $tableShameID
     * @param array $options
     * @return EntityManagerInterface
     */
    public function create() : EntityManagerInterface
    {
        $em = $this->container->make(EntityManagerInterface::class, [
            'crud' => $this->container->make(CrudInterface::class, [
                'dataMapper' => $this->container->make(DataMapperFactory::class, [
                    'dataMapperEnvConfig' => $this->dataMapperEnvConfig,
                ])->create(),
                'queryBuilder' => $this->container->make(QueryBuilderInterface::class),
                'tableSchema' => $this->tableSchema,
                'tableSchemaID' => $this->tableSchemaID,
                'options' => $this->options,
            ]),
        ]);
        if (!$em instanceof EntityManagerInterface) {
            throw new EntityManagerExceptions(get_class($em) . ' is not a valid entityManager object!');
        }

        return $em;
    }

    /**
     * Get main constructor.
     */
    public function getDataMapper() : DataMapperInterface
    {
        return $this->dataMapper;
    }

    /**
     * Get main constructor.
     */
    public function getQueryMuilder() :QueryBuilderInterface
    {
        return $this->queryBuilder;
    }
}
