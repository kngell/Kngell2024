<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$reader = new PropertyReader();
$properties = $reader->readProperties(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Properties.yaml');

if (! isset($properties['doctrine']) || ! isset($properties['doctrine']['orm'])) {
    echo 'Cannot execute Command. Not All properties are setted.';
    die;
}

$ormProps = $properties['doctrine']['orm'];
$connectionOptions = array_filter([
    'driver' => $ormProps['driver'] ?? null,
    'driverClass' => $ormProps['driverClass'] ?? null,
    'user' => $ormProps['user'] ?? null,
    'password' => $ormProps['password'] ?? null,
    'driverOptions' => $ormProps['driverOptions'] ?? null,
    'wrapperClass' => $ormProps['wrapperClass'] ?? null,
    'path' => isset($ormProps['sqlite']['path']) ? dirname(__DIR__) . DIRECTORY_SEPARATOR . $ormProps['sqlite']['path'] : null,
]);

$metaDataConfig = ORMSetup::createAttributeMetadataConfiguration(
    array_map(fn ($path) => dirname(__DIR__) . DIRECTORY_SEPARATOR . $path, $ormProps['scanPaths'] ?? []),
    true
);

$connection = DriverManager::getConnection($connectionOptions, $metaDataConfig);
$entityManager = new EntityManager($connection, $metaDataConfig);
ConsoleRunner::run(
    new SingleManagerProvider($entityManager)
);