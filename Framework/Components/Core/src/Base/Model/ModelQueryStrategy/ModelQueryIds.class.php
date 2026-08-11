<?php

declare(strict_types=1);

class ModelQueryIds extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        $processed = $this->utils->processConditions($entity, $params['conditions'] ?? []);
        $page = $params['page'] ?? 1;
        $perPage = $params['perPage'] ?? 10;
        $offset = max(0, ($page - 1) * $perPage);
        $keyField = $params['keyField'] ?? null;

        $em->getRepository($processed->entity)->fetchIds($processed->conditions, $perPage, $offset, $keyField);

        return $this->getQueryResult($em, 'page')
            ->setPagination($page, $perPage)->setEntityKeyField($keyField);
    }
}