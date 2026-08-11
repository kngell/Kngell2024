<?php

declare(strict_types=1);

class ModelQueryCount extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        $processed = $this->utils->processConditions($entity, $params);
        $em->getRepository($processed->entity)->count($processed->conditions);
        return $this->getQueryResult($em, 'count');
    }
}