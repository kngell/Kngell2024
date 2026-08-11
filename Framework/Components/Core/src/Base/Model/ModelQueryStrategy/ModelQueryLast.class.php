<?php

declare(strict_types=1);

class ModelQueryLast extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        $processed = $this->utils->processConditions($entity, $params);
        $limit = $params['limit'] ?? 1;
        $em->getRepository($processed->entity)->findAll($processed->conditions);
        return $this->getQueryResult($em)->setOperation('last')
          ->setLastLimit($limit);
    }
}