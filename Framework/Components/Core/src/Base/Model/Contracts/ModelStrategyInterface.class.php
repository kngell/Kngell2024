<?php

declare(strict_types=1);
interface ModelStrategyInterface
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult;
}