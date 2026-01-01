<?php

declare(strict_types=1);

interface EntityDependenciesFactoryInterface
{
    public function getMapper(): EntityMapperInterface;

    public function getNormalizer(): TypeNormalizerInterface;

    public function getChangeTracker(): ChangeTrackerInterface;

    public function getHydrator(): EntityHydratorInterface;

    public function getRelationManager(): EntityRelationManagerInterface;

    public function getTransformer(): EntityToArrayTransformerInterface;

    // public function getTypeHandlerFactory(): TypeHandlerFactory;

    public function getTypePresenterFactory(): TypePresenterFactoryInterface;

    public function getEntityFactory(): EntityFactoryInterface;
}