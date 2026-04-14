<?php

declare(strict_types=1);

class ModelOperationUpdate extends AbstractModelBaseOperations
{
    public function execute(EntityManagerInterface $em, Entity $prototype, mixed $params): QueryResult
    {
        $data = $params['data'] ?? [];
        $conditions = $params['conditions'] ?? [];

        $payload = $data instanceof ModelOperationPayload
            ? $data
            : $this->utils->normalizeData($data, $prototype);

        if (!$payload->isCollection()) {
            list($em, $lastUpdateId, $skipped) = $this->handleUpdate($em, $payload, $conditions);
        } else {
            list($em, $lastUpdateId, $skipped) = $this->handleBulkUpdate($em, $payload, $conditions);
        }
        $results = $this->getQueryResult($em, $skipped)->setLastUpdateId($lastUpdateId);
        if ($skipped) {
            $results->setSkipped(true, 'No changes detected in entity fields.');
        }
        return $results;
    }

    private function handleUpdate(
        EntityManagerInterface $em,
        ModelOperationPayload $payload,
        array $conditions,
    ): array {
        $data = $payload->getData();
        $keyField = $payload->getKeyProperty();
        if (!$keyField) {
            throw new DataAccessLayerException('Entity has no key field defined');
        }
        $id = $payload->getUpdateId();
        /** @var null|Entity $dbEntity */
        $dbEntity = $this->md->getFromIdentityMap($id);

        if ($dbEntity === null) {
            $dbResult = $this->md->find($id);
            if (!$dbResult->exists()) {
                throw new DataAccessLayerException("Entity with ID {$id} not found!");
            }
            $dbEntity = $dbResult->asClass();
            $this->md->addToIdentityMap($dbEntity);
            $dbEntity->track();
        } else {
            if (!$dbEntity->isTracking()) {
                $dbEntity->track();
            }
        }
        if (is_array($data)) {
            $dbEntity->assign($data);
        } elseif ($data instanceof Entity) {
            $dbEntity->assign($data->toArray());
        } else {
            throw new DataAccessLayerException('Invalid data type for update');
        }
        $skipped = false;
        if ($dbEntity->hasChanges($dbEntity)) {
            $this->utils->updateTimestamps($dbEntity);

            $em->setEntity($dbEntity);
            list($defaultEntity, $processedConditions) = $this->utils->processConditions($em->getEntity(), $conditions);
            $em->getRepository($defaultEntity)->update($defaultEntity->table(), $processedConditions);
        } else {
            $skipped = true;
        }

        $lastUpdateId = $dbEntity->getEntityPrimarykeyValue();
        $dbEntity->stopTracking();
        return [$em, $lastUpdateId, $skipped];
    }

    private function handleBulkUpdate(
        EntityManagerInterface $em,
        ModelOperationPayload $payload,
        array $conditions = [],
    ): array {
        $keyField = $payload->getKeyField();
        $updatesById = $payload->getUpdatesById();

        $ids = $payload->getIds();

        if (empty($conditions)) {
            $conditions = [$keyField => array_unique($ids)];
        }
        $dbEntities = $this->md->all($conditions)->asClass();

        $collection = new Collection();
        $targetedIds = [];

        /** @var Entity $dbEntity */
        foreach ($dbEntities as $dbEntity) {
            $id = $dbEntity->getEntityPrimarykeyValue();
            $targetedIds[] = $id;
            if (isset($updatesById[$id])) {
                $dbEntity->track();
                $dbEntity->assign($updatesById[$id]);

                $hasChanges = $dbEntity->hasChanges();
                if ($hasChanges) {
                    $this->utils->updateTimestamps($dbEntity);
                    $collection->add($dbEntity);
                } else {
                    $dbEntity->stopTracking();
                }
            }
        }

        $skipped = $collection->isEmpty();

        if (!$skipped) {
            $em->setEntity($collection);
            $em->getRepository($collection->first())->bulkUpdate($em->table(), [], []);
        }

        return [$em, $targetedIds, $skipped];
    }
}