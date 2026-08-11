<?php

declare(strict_types=1);

class ModelQueryFirst extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        $processed = $this->utils->processConditions($entity, $params);
        $limit = $params['limit'] ?? 1;
        $em->getRepository($processed->entity)->findBy($processed->conditions, $limit, 0);
        return $this->getQueryResult($em, 'first')->setLimit($limit);
    }
}