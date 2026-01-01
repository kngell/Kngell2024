<?php

declare(strict_types=1);

class ModelQueryAll extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        [$targetEntity, $conditions] = $this->utils->processConditions($entity, $params);
        $em->getRepository($targetEntity)->findAll($conditions);
        return $this->getQueryResult($em, 'all');
    }
}