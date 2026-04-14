<?php

declare(strict_types=1);

class ModelOperationDelete extends AbstractModelBaseOperations
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        [$targetEntity, $conditions,$deleteOption] = $this->utils->processConditions($entity, $params);

        if ($targetEntity instanceof SoftDeletableInterface && $deleteOption === 'archive') {
            return $this->softDelete($em, $targetEntity, $conditions);
        }

        return $this->hardDelete($em, $targetEntity, $conditions);
    }

    private function softDelete(EntityManagerInterface $em, SoftDeletableInterface|Entity $entity, array $conditions = []): QueryResult
    {
        if ($entity->isDeleted()) {
            $result = $this->getQueryResult($em);
            $result->setSkipped(true, 'Entity is already soft deleted');
            return $result;
        }
        if (!$entity->isTracking()) {
            $entity->track();
        }

        $entity->softDelete();

        if ($entity instanceof TimestampableInterface) {
            $entity->setUpdatedAt(new DateTimeImmutable());
        }

        $em->setEntity($entity);
        $repository = $em->getRepository($entity);
        $repository->update($em->table(), $conditions);

        $result = $this->getQueryResult($em);

        if ($result->getAffectedRows() === 0) {
            $result->setSkipped(true, 'No records found to delete');
        }

        return $result;
    }

    private function hardDelete(EntityManagerInterface $em, Entity $entity, array $conditions = []): QueryResult
    {
        $repository = $em->getRepository($entity);
        $repository->delete($conditions);

        $result = $this->getQueryResult($em);

        if ($result->getAffectedRows() === 0) {
            $result->setSkipped(true, 'No records found to delete');
        }

        return $result;
    }
}