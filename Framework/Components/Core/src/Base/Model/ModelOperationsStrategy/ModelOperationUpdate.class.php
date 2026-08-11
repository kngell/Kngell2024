<?php

declare(strict_types=1);

class ModelOperationUpdate extends AbstractModelBaseOperations
{
    public function execute(EntityManagerInterface $em, Entity $prototype, mixed $params): QueryResult
    {
        if (!array_key_exists('data', $params)) {
            throw new DataAccessLayerException('Missing data for update operation.');
        }

        $data = $params['data'];
        $conditions = $params['conditions'] ?? [];

        $payload = $data instanceof ModelOperationPayload
            ? $data
            : $this->utils->normalizeData($data, $prototype);

        if ($payload->isConditionalOnly()) {
            $update = $this->handleConditionalUpdate($em, $payload, $prototype, $conditions);
        } elseif (!$payload->isCollection()) {
            $update = $this->handleUpdate($em, $payload, $conditions);
        } else {
            $update = $this->handleBulkUpdate($em, $payload, $prototype, $conditions);
        }

        $results = $this->getQueryResult($update->em, $update->skipped)->setLastUpdateId($update->lastUpdateId);

        if ($this->md->currentOperation() === 'delete') {
            return $results->setSkipped($update->skipped);
        }
        if ($update->skipped) {
            $results->setSkipped(true, 'No changes detected in entity fields.');
        }
        return $results;
    }

    private function handleUpdate(
        EntityManagerInterface $em,
        ModelOperationPayload $payload,
        array $conditions,
    ): ModelUpdateResult {
        if (!$payload->getKeyProperty()) {
            throw new DataAccessLayerException('Entity has no key field defined');
        }
        $normalizedEntity = $payload->getData();
        $id = $payload->getUpdateId();

        // ---- load + track ----
        /** @var null|Entity $dbEntity */
        $dbEntity = $this->md->getFromIdentityMap($id);
        if ($dbEntity === null) {
            $dbResult = !empty($conditions) ? $this->md->one($conditions) : $this->md->find($id);
            if (!$dbResult->exists()) {
                return new ModelUpdateResult(
                    em: $em,
                );
            }
            $dbEntity = $dbResult->asClass();
            $this->md->addToIdentityMap($dbEntity);
        }
        if (!$dbEntity->isTracking()) {
            $dbEntity->track();
        }

        // ---- apply incoming data ----
        if ($normalizedEntity instanceof Entity) {
            $dbEntity->assign($normalizedEntity->toArray());
        }

        // ---- decide whether to flush ----
        $skipped = true;
        if ($dbEntity->hasChanges()) {
            $archivedAt = $this->md->getArchiveAt();
            $this->utils->updateTimestamps($dbEntity, $archivedAt);

            $processed = $this->utils->processConditions($dbEntity, $conditions);
            $em->setEntity($processed->entity);

            $em->getRepository($processed->entity)
               ->update($processed->entity->table(), $processed->conditions);
            $skipped = false;
        }

        $lastUpdateId = $dbEntity->getEntityPrimarykeyValue();
        $dbEntity->stopTracking();

        return new ModelUpdateResult(
            em: $em,
            lastUpdateId: $lastUpdateId,
            skipped:$skipped,
        );
    }

    private function handleBulkUpdate(
        EntityManagerInterface $em,
        ModelOperationPayload $payload,
        Entity $prototype,
        array $conditions = [],
    ): ModelUpdateResult {
        $keyField = $payload->getKeyField();
        $updatesById = $payload->getUpdatesById();
        $ids = $payload->getIds();
        $data = $payload->getData();

        if (empty($conditions)) {
            $conditions = [$keyField => array_unique($ids)];
        }

        $processed = $this->utils->processConditions($prototype, $conditions);

        $dbEntities = $this->md->all($processed->conditions)->asClass();
        $this->md->addToIdentityMap($dbEntities);

        $collection = new Collection();
        $targetedIds = [];
        $tracked = [];

        /** @var Entity $dbEntity */
        foreach ($dbEntities as $dbEntity) {
            $id = $dbEntity->getEntityPrimarykeyValue();
            $targetedIds[] = $id;

            if (!isset($updatesById[$id])) {
                continue;
            }

            $dbEntity->track();
            $tracked[] = $dbEntity;

            $data = $updatesById[$id] instanceof Entity ? $updatesById[$id]->toArray() : $updatesById[$id];
            $dbEntity->assign($data);

            if ($dbEntity->hasChanges()) {
                $archivedAt = $this->md->getArchiveAt();
                $this->utils->updateTimestamps($dbEntity, $archivedAt);
                $collection->add($dbEntity);
            }
        }

        $skipped = $collection->isEmpty();

        if (!$skipped) {
            $em->setEntity($collection);

            $em->getRepository($collection->first())
               ->bulkUpdate(
                   $em->table(),
                   [],
                   [],
               );
        }

        // Symmetric tracking lifecycle.
        foreach ($tracked as $entity) {
            $entity->stopTracking();
        }
        return new ModelUpdateResult(
            em: $em,
            lastUpdateId: $targetedIds,
            skipped:$skipped,
        );
    }

    private function handleConditionalUpdate(
        EntityManagerInterface $em,
        ModelOperationPayload $payload,
        Entity $prototype,
        array $conditions,
    ): ModelUpdateResult {
        if (empty($conditions)) {
            throw new DataAccessLayerException('Conditions required for conditional update');
        }

        $currentOperation = $this->md->currentOperation();
        $archivedAt = $this->md->getArchiveAt();

        // Build update data with timestamps based on operation
        $entity = $this->buildUpdateDataWithTimestamps(
            $payload->getData(),
            $currentOperation,
            $archivedAt,
            $prototype,
        );

        // Process conditions
        $processed = $this->utils->processConditions($entity, $conditions);

        // Execute direct UPDATE
        $em->getRepository($entity)
            ->conditionalUpdate(
                table: $prototype->table(),
                data: $entity,
                conditions: $processed->conditions,
            );

        return new ModelUpdateResult(
            em: $em,
            lastUpdateId: null,
            skipped: false,
        );
    }

    private function buildUpdateDataWithTimestamps(
        mixed $data,
        string $currentOperation,
        ?DateTimeImmutable $archivedAt = null,
        ?Entity $prototype = null,
    ): Entity {
        if ($prototype === null) {
            throw new DataAccessLayerException('Prototype required for building update data');
        }

        $entity = clone $prototype;
        $isSoftDelete = ($currentOperation === 'soft_delete');

        if ($data instanceof Entity) {
            $entity->assign($data->toArray());
        } elseif (is_array($data) && ArrayUtils::isAssoc($data)) {
            $entity->assign($data);
        } else {
            throw new DataAccessLayerException('Invalid data type for conditional update');
        }
        if ($entity instanceof TimestampableInterface) {
            if ($isSoftDelete && $archivedAt !== null) {
                $entity->setUpdatedAt($archivedAt);
            } else {
                $entity->setUpdatedAt(new DateTimeImmutable());
            }
        }
        return $entity;
    }
}