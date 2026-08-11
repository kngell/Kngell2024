<?php

declare(strict_types=1);

class ModelUtility implements ModelUtilityInterface
{
    public function normalizeData(mixed $data, Entity $prototype): ModelOperationPayload
    {
        return ModelOperationPayload::create($data, $prototype);
    }

    public function processConditions(Entity $defaultEntity, mixed $params): ProcessedConditions
    {
        if ($params instanceof Entity) {
            return new ProcessedConditions($params, []);
        }

        if (is_array($params)) {
            if ($params === []) {
                $conditions = $defaultEntity->entityKeyIsInitialzed()
                    ? [$defaultEntity->getEntityKeyField() => $defaultEntity->getEntityPrimarykeyValue()]
                    : [];
                return new ProcessedConditions($defaultEntity, $conditions);
            }

            $deleteOption = null;
            $archivedAt = null;
            $explicit = null;

            if (array_key_exists('deleteOption', $params)) {
                $deleteOption = $params['deleteOption'];
                unset($params['deleteOption']);
            }
            if (array_key_exists('archived_at', $params)) {
                $archivedAt = $params['archived_at'];
                unset($params['archived_at']);
                if (is_string($archivedAt) && $archivedAt !== '') {
                    $archivedAt = new DateTimeImmutable($archivedAt);
                }
            }
            if (array_key_exists('conditions', $params)) {
                $explicit = $params['conditions'];
                unset($params['conditions']);
            }

            return new ProcessedConditions(
                $defaultEntity,
                $explicit ?? $params,
                $deleteOption,
                $archivedAt instanceof DateTimeImmutable ? $archivedAt : null,
            );
        }

        if (is_string($params) || is_int($params)) {
            $field = $defaultEntity->getEntityKeyField() ?? 'id';
            return new ProcessedConditions($defaultEntity, [$field => $params]);
        }

        return new ProcessedConditions($defaultEntity, []);
    }

    public function updateTimestamps(Entity|array|CollectionInterface $entity, ?DateTimeImmutable $at = null): void
    {
        // Keep your existing logic
        if ($entity instanceof TimestampableInterface) {
            if (method_exists($entity, 'touchTimestamps')) {
                $entity->touchTimestamps($at);
            }
            return;
        }

        foreach ($entity as $item) {
            if ($item instanceof TimestampableInterface) {
                $item->touchTimestamps();
            }
        }
    }
}