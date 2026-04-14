<?php

declare(strict_types=1);

class ModelQueryPage extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        $columns = [];
        if (array_key_exists('columns', $params)) {
            $columns = $params['columns'];
            unset($params['columns']);
        }
        $page = $params['page'] ?? 1;
        $perPage = $params['perPage'] ?? 10;
        $offset = max(0, ($page - 1) * $perPage);
        [$targetEntity, $conditions] = $this->utils->processConditions($entity, $params['conditions'] ?? []);

        $em->getRepository($targetEntity)->findBy($conditions, $perPage, $offset, $columns);

        return $this->getQueryResult($em, 'page')
            ->setPagination($page, $perPage);
    }
}