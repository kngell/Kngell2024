<?php

declare(strict_types=1);

class ModelQueryAll extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        $columns = [];
        $limit = null;
        $offset = null;
        if (array_key_exists('columns', $params)) {
            $columns = $params['columns'];
            unset($params['columns']);
        }
        if (array_key_exists('limit', $params)) {
            $limit = $params['limit'];
            unset($params['limit']);
        }
        if (array_key_exists('offset', $params)) {
            $offset = $params['offset'];
            unset($params['offset']);
        }
        $queryPayload = ModelQueryPayload::create($entity, $params);
        $conditions = $queryPayload->getConditions();
        $processed = $this->utils->processConditions($entity, $conditions);
        $em->getRepository($processed->entity)->findAll($processed->conditions, $limit, $offset, $columns);
        return $this->getQueryResult($em, 'all');
    }
}