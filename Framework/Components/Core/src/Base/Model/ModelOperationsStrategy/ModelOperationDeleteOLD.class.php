<?php

declare(strict_types=1);

class ModelOperationDeleteOLD extends AbstractModelBaseOperations
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        [$targetEntity, $conditions] = $this->utils->processConditions($entity, $params);

        if ($targetEntity instanceof SoftDeletableInterface) {
            return $this->softDelete($em, $targetEntity, $conditions);
        }

        $em->getRepository($targetEntity)->delete($conditions);
        return $this->getQueryResult($em);
    }

    private function softDelete(EntityManagerInterface $em, SoftDeletableInterface|Entity $entity, array $conditions = []): QueryResult
    {
        if ($entity instanceof SoftDeletableInterface) {
            $entity->softDelete();
        }

        if ($entity instanceof TimestampableInterface) {
            $entity->setUpdatedAt(new DateTimeImmutable());
        }

        $em->setEntity($entity);
        $em->getRepository($entity)->update($em->table(), $conditions);
        return $this->getQueryResult($em);
    }
}