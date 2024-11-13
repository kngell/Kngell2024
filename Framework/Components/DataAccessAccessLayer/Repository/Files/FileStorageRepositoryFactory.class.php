<?php

declare(strict_types=1);

class FileStorageRepositoryFactory
{
    protected ContainerInterface $container;

    public function create() : FileStorageRepositoryInterface
    {
        $repositoryObject = $this->container->make(FileStorageRepository::class);
        if (!$repositoryObject instanceof FileStorageRepositoryInterface) {
            throw new BaseUnexpectedValueException(get_class($repositoryObject) . ' is not a valid repository Object!');
        }

        return $repositoryObject;
    }
}
