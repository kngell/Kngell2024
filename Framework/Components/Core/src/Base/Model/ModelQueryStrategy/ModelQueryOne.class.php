<?php

declare(strict_types=1);

class ModelQueryOne extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        [$targetEntity, $conditions] = $this->utils->processConditions($entity, $params);
        $em->getRepository($targetEntity)->findOneBy($conditions);
        return $this->getQueryResult($em, 'single');
    }
}