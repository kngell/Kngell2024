<?php

declare(strict_types=1);

class DataMapperFactory
{
    private ContainerInterface $container;
    private DataMapperEnvironmentConfig $dataMapperEnvConfig;

    /**
     * Main constructor
     * ================================================================.
     *@return void
     */
    public function __construct(DataMapperEnvironmentConfig $dataMapperEnvConfig)
    {
        $this->dataMapperEnvConfig = $dataMapperEnvConfig;
    }

    /**
     * Create method
     * ============================================================.
     * @param string $databaseConnexionObject
     * @param string $dataMapperEnvConfigObject
     *@return DataMapperInterface
     */
    public function create() : DataMapperInterface
    {
        $dataMapperObject = $this->container->make(DataMapperInterface::class, [
            '_con' => $this->container->make(DatabaseConnexionInterface::class, [
                'credentials' => $this->dataMapperEnvConfig->getCredentials('mysql'),
            ]),
        ]);
        if (!$dataMapperObject instanceof DataMapperInterface) {
            throw new DataMapperExceptions(DataMapperInterface::class . ' is not a valid database connexion Object!');
        }

        return $dataMapperObject;
    }
}
