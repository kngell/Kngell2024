<?php

declare(strict_types=1);

class ModelOperationUpdate extends AbstractModelBaseOperations
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $data): QueryResult
    {
        if (is_array($data) && isset($data['conditions'])) {
            [$targetEntity, $conditions] = $this->utils->processConditions($entity, $data['conditions']);
            $em->getRepository($targetEntity)->update($conditions);
        } else {
            $entityToUpdate = $this->utils->prepareForSave($entity, $data);
            $em->getRepository($entityToUpdate)->update([]);
        }

        return $this->getQueryResult($em);
    }
}