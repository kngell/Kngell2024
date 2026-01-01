<?php

declare(strict_types=1);

class ModelOperationInsert extends AbstractModelBaseOperations
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $data): QueryResult
    {
        $entityToInsert = $data ?? $em->getEntity();
        $em->getRepository($entityToInsert)->create();
        return $this->getQueryResult($em);
    }
}