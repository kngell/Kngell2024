<?php

declare(strict_types=1);

class ModelQueryCount extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        [$entity, $processedConditions] = $this->utils->processConditions($entity, $params);
        $em->getRepository($entity)->count($processedConditions);
        return $this->getQueryResult($em, 'count');
    }
}