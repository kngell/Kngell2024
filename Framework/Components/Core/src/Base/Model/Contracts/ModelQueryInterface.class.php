<?php

declare(strict_types=1);

interface ModelQueryInterface
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult;
}