<?php

declare(strict_types=1);

class ModelQueryOne extends AbstractModelBaseQuery
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        // dd($params);
        $queryPayload = ModelQueryPayload::create($entity, $params);
        $conditions = $queryPayload->getConditions();
        if ($queryPayload->getDeleteOption()) {
            $conditions['delete_option'] = $queryPayload->getDeleteOption();
        }
        if ($queryPayload->getArchivedAt()) {
            $conditions['archived_at'] = $queryPayload->getArchivedAt();
        }
        $processed = $this->utils->processConditions($entity, $conditions);
        $em->getRepository($processed->entity)->findOneBy($processed->conditions);
        return $this->getQueryResult($em, 'single');
    }
}