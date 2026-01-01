<?php

declare(strict_types=1);

class ModelQueryPage extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        [$targetEntity, $conditions] = $this->utils->processConditions($entity, $params['conditions'] ?? []);
        $page = $params['page'] ?? 1;
        $perPage = $params['perPage'] ?? 10;
        $offset = max(0, ($page - 1) * $perPage);

        $em->getRepository($targetEntity)->findBy($conditions, $perPage, $offset);

        return $this->getQueryResult($em, 'page')
            ->setPagination($page, $perPage);
    }
}