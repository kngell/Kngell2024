<?php

declare(strict_types=1);

class ModelOperationDelete extends AbstractModelBaseOperations
{
    public function execute(EntityManagerInterface $em, Entity $entity, mixed $params): QueryResult
    {
        if (array_key_exists('data', $params)) {
            $data = $params['data'];
            unset($params['data']);
        }
        $processed = $this->utils->processConditions($entity, $params);

        if ($processed->entity instanceof SoftDeletableInterface && $processed->deleteOption === 'archive') {
            return $this->softDelete($em, $processed, $data ?? []);
        }

        return $this->hardDelete($em, $processed->entity, $processed->conditions);
    }

    private function softDelete(
        EntityManagerInterface $em,
        ProcessedConditions $processed,
        array $data = [],
    ): QueryResult {
        /** @var Entity|SoftDeletableInterface */
        $prototype = $processed->entity;

        if ($prototype->isDeleted()) {
            $result = $this->getQueryResult($em);
            $result->setSkipped(true, 'Entity is already soft deleted');
            return $result;
        }

        // Store archive timestamp in ModelData for Update operation to use
        $archivedAt = new DateTimeImmutable();
        $this->md->setArchiveAt($archivedAt);
        $this->md->setCurrentOperation('soft_delete');

        // Prepare entity with data (but NOT with timestamps)
        $entity = null;
        $collection = null;

        if (is_array($data)) {
            if (ArrayUtils::isAssoc($data)) {
                $entity = clone $prototype;
                $entity->assign($data);
                $entity->softDelete($archivedAt);
            } elseif (ArrayUtils::isArrayList($data)) {
                $collection = new Collection();
                foreach ($data as $singleDataSet) {
                    $item = clone $prototype;
                    $item->assign($singleDataSet);
                    $item->softDelete($archivedAt);
                    $collection->add($item);
                }
            } elseif ($data === []) {
                $entity = clone $prototype;
                $entity->softDelete($archivedAt);
            }
        }

        $entity = $collection ?? $entity ?? $prototype;

        if ($entity === null) {
            $reason = 'No data to archive';
            return $this->getQueryResult($em, true, $reason);
        }

        $result = $this->md->update($entity, $processed->conditions);

        if ($result->getAffectedRows() === 0) {
            $reason = $entity instanceof SoftDeletableInterface
                ? 'No active records to archive'
                : 'No records found to delete';
            $result->setSkipped(true, $reason);
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