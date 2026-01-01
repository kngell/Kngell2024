<?php

declare(strict_types=1);

// Helper class for custom dependencies
class CustomEntityDependencies implements EntityDependenciesFactoryInterface
{
    public function __construct(
        private EntityDependenciesFactoryInterface $baseDependencies,
        private ?EntityMapperInterface $mapper = null,
        private ?EntityHydratorInterface $hydrator = null,
        private ?EntityRelationManagerInterface $relationManager = null,
    ) {
    }

    public function getMapper(): EntityMapperInterface
    {
        return $this->mapper ?? $this->baseDependencies->getMapper();
    }

    public function getHydrator(): EntityHydratorInterface
    {
        return $this->hydrator ?? $this->baseDependencies->getHydrator();
    }

    public function getRelationManager(): EntityRelationManagerInterface
    {
        return $this->relationManager ?? $this->baseDependencies->getRelationManager();
    }

    // Delegate all other methods to base dependencies
    public function getNormalizer(): TypeNormalizerInterface
    {
        return $this->baseDependencies->getNormalizer();
    }

    public function getChangeTracker(): ChangeTrackerInterface
    {
        return $this->baseDependencies->getChangeTracker();
    }

    public function getTransformer(): EntityToArrayTransformerInterface
    {
        return $this->baseDependencies->getTransformer();
    }

    public function getTypeHandlerFactory(): TypeHandlerFactory
    {
        return $this->baseDependencies->getTypeHandlerFactory();
    }

    public function getTypePresenterFactory(): TypePresenterFactory
    {
        return $this->baseDependencies->getTypePresenterFactory();
    }

    public function getEntityFactory(): EntityFactoryInterface
    {
        return $this->baseDependencies->getEntityFactory();
    }
}