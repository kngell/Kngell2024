<?php

declare(strict_types=1);

final class ContainerRegistry
{
    private static ?ContainerInterface $instance = null;

    public static function setContainer(ContainerInterface $container): void
    {
        self::$instance = $container;
    }

    public static function getContainer(): ContainerInterface
    {
        if (self::$instance === null) {
            throw new RuntimeException('Container not initialized');
        }
        return self::$instance;
    }
}