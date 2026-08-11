<?php

declare(strict_types=1);

class ModelOperationRestore extends AbstractModelBaseOperations
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        [$targetEntity, $conditions,$deleteOption , $archivedAt] = $this->utils->processConditions($entity, $params);

        if (!$targetEntity instanceof Entity) {
            throw new DataAccessLayerException(sprintf(
                'Cannot restore: %s is not an entity.',
                $targetEntity::class,
            ));
        }
        if (!$targetEntity->isTracking()) {
            $targetEntity->track();
        }
        if (!$targetEntity instanceof SoftDeletableInterface) {
            throw new DataAccessLayerException(sprintf(
                'Cannot restore: %s does not implement SoftDeletableInterface.',
                $targetEntity::class,
            ));
        }

        if ($archivedAt === null) {
            throw new InvalidArgumentException(
                'Restore operations require archived_at to scope the WHERE clause. '
                . 'Without it, the restore would un-archive every row matching the conditions, '
                . 'including ones archived independently.',
            );
        }

        $targetEntity->restore();

        if ($targetEntity instanceof TimestampableInterface) {
            $targetEntity->setUpdatedAt(new DateTimeImmutable());
        }

        $conditions['deleted_at'] = $archivedAt->format($targetEntity->getDateFormat());

        $em->setEntity($targetEntity);
        $repository = $em->getRepository($targetEntity);
        $repository->update($em->table(), $conditions);

        $result = $this->getQueryResult($em);
        if ($result->getAffectedRows() === 0) {
            $result->setSkipped(true, 'No matching archived records to restore');
        }

        return $result;
    }
}