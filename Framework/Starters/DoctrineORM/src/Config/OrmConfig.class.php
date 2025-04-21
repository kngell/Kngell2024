<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

#[Configuration]
readonly class OrmConfig
{
    public function __construct(
        #[Property('kngell-ecom.rootDirectory')]
        private string $rootPath,
        #[Property('kngell-ecom.cacheDirectory')]
        private string $cachePath,
        #[Property('doctrine.orm.driver')]
        private string|null $driver,
        #[Property('doctrine.orm.driverClass')]
        private string|null $driverClass,
        #[Property('doctrine.orm.user')]
        private string|null $user,
        #[Property('doctrine.orm.password')]
        private string|null $password,
        #[Property('doctrine.orm.wrapperClass')]
        private string|null $wrapperClass,
        #[Property('doctrine.orm.sqlite.path')]
        private string|null $sqlitePath,
        #[Property('doctrine.orm.scanPaths')]
        private array $scanPaths = [],
        #[Property('doctrine.orm.driverOptions')]
        private array $driverOptions = [],
        #[Property('production')]
        private bool $production = false,
    ) {
    }

    #[Bean]
    public function EntityManager() : EntityManager
    {
        $connectionOptions = array_filter([
            'driver' => $this->driver,
            'driverClass' => $this->driverClass,
            'user' => $this->user,
            'password' => $this->password,
            'driverOptions' => $this->driverOptions,
            'wrapperClass' => $this->wrapperClass,
            'path' => $this->sqlitePath !== null ? $this->rootPath . DIRECTORY_SEPARATOR . $this->sqlitePath : null,
        ]);
        $cache = new FilesystemAdapter('', 0, $this->cachePath . DIRECTORY_SEPARATOR . 'DoctrineORM' . DIRECTORY_SEPARATOR . 'Cache');
        $metaDataConfig = ORMSetup::createAttributeMetadataConfiguration(
            array_map(fn ($path) => $this->rootPath . DIRECTORY_SEPARATOR . $path, $this->scanPaths),
            ! $this->production,
            null,
            $cache
        );
        $connection = DriverManager::getConnection($connectionOptions, $metaDataConfig);
        return new EntityManager($connection, $metaDataConfig);
    }
}