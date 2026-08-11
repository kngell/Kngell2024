<?php

declare(strict_types=1);

class ModelQueryGet extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        $processed = $this->utils->processConditions($entity, $params);

        $limit = $params['limit'];
        $em->getRepository($processed->entity)->findBy($processed->conditions, $limit, 0);

        return $this->getQueryResult($em, 'all')->setLimit($limit);
    }
}