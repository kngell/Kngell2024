<?php

declare(strict_types=1);

class ModelQueryFind extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        $em->getRepository($entity)->findByID($params);
        return $this->getQueryResult($em, 'single');
    }
}