<?php

declare(strict_types=1);

class ModelOperationDelete extends AbstractModelBaseOperations
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        [$targetEntity, $conditions] = $this->utils->processConditions($entity, $params);

        if ($targetEntity instanceof Entity) {
            return $this->softDelete($em, $targetEntity);
        }

        $em->getRepository($targetEntity)->delete($conditions);
        return $this->getQueryResult($em);
    }

    private function softDelete(EntityManagerInterface $em, Entity $entity): QueryResult
    {
        if ($entity instanceof SoftDeletableInterface) {
            $entity->softDelete();
        }

        if ($entity instanceof TimestampableInterface) {
            $entity->setUpdatedAt(new DateTimeImmutable());
        }

        $em->setEntity($entity);
        $em->getRepository($entity)->update([]);

        return $this->getQueryResult($em);
    }
}