<?php

declare(strict_types=1);

class ModelQueryAll extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        $columns = [];
        if (array_key_exists('columns', $params)) {
            $columns = $params['columns'];
            unset($params['columns']);
        }
        [$targetEntity, $conditions] = $this->utils->processConditions($entity, $params);
        $em->getRepository($targetEntity)->findAll($conditions, $columns);
        return $this->getQueryResult($em, 'all');
    }
}
