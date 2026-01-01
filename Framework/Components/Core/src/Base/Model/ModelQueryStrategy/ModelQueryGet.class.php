<?php

declare(strict_types=1);

class ModelQueryGet extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        [$entity, $processedConditions] = $this->utils->processConditions($entity, $params);

        $limit = $params['limit'];
        $em->getRepository($entity)->findBy($processedConditions, $limit, 0);

        return $this->getQueryResult($em, 'all')->setLimit($limit);
    }
}