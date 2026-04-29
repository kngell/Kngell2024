<?php

declare(strict_types=1);

class ModelUtility implements ModelUtilityInterface
{
    public function normalizeData(mixed $data, Entity $prototype): ModelOperationPayload
    {
        return ModelOperationPayload::create($data, $prototype);
    }

    public function processConditions(Entity $defaultEntity, mixed $params): array
    {
        // Keep your existing logic
        if ($params instanceof Entity) {
            return [$params, []];
        }

        if (is_array($params)) {
            if (!empty($params)) {
                $deleteOption = null;
                if (array_key_exists('deleteOption', $params)) {
                    $deleteOption = $params['deleteOption'];
                    unset($params['deleteOption']);
                }
                if (array_key_exists('conditions', $params)) {
                    $conditions = $params['conditions'];
                    unset($params['conditions']);
                }
                $conditions = $conditions ?? $params;
                return [$defaultEntity, $conditions, $deleteOption];
            } else {
                if ($defaultEntity->entityKeyIsInitialzed()) {
                    $params = [$defaultEntity->getEntityKeyField() => $defaultEntity->getEntityPrimarykeyValue()];
                } else {
                    $params = [];
                }

                return[$defaultEntity, $params, null];
            }
        }

        if (is_string($params) || is_int($params)) {
            $fieldId = $defaultEntity->getEntityKeyField() ?? 'id';
            return [$defaultEntity, [$fieldId => $params], null];
        }

        return [$defaultEntity, [], null];
    }

    public function updateTimestamps(Entity|array|CollectionInterface $entity): void
    {
        // Keep your existing logic
        if ($entity instanceof TimestampableInterface) {
            if (method_exists($entity, 'touchTimestamps')) {
                $entity->touchTimestamps();
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