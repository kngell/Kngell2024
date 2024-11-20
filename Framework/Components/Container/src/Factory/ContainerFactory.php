<?php

declare(strict_types=1);

/** PSR-11 Container */
class ContainerFactory
{
    /** @var array */
    protected array $providers = [];

    /** @return void */
    public function __construct()
    {
    }

    /**
     * Factory method which creates the container object.
     *
     * @param string|null $container
     * @return ContainerInterface
     */
    public function create(?string $container = null): ContainerInterface
    {
        $containerObject = ($container != null) ? new $container() : new Container();
        if (!$containerObject instanceof ContainerInterface) {
            throw new ContainerInvalidArgumentException($container . ' is not a valid container object');
        }

        return $containerObject;
    }
}
