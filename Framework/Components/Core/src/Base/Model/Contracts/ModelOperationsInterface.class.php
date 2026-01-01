<?php

declare(strict_types=1);

interface ModelOperationsInterface
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $data): QueryResult;
}