<?php

declare(strict_types=1);

interface ModelContextInterface
{
    public function execute(string $operation, EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult;

    public function register(string $name, ModelStrategyInterface $strategy, string $group = 'custom'): void;

    public function initialize(Model $md, ModelUtilityInterface $utils): void;
}