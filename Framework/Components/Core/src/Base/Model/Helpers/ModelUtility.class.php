<?php

declare(strict_types=1);

class ModelUtility implements ModelUtilityInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function processConditions(Entity $defaultEntity, mixed $params): array
    {
        if ($params instanceof Entity) {
            return [$params, []];
        }

        if (is_array($params)) {
            return [$defaultEntity, $params];
        }

        if (is_string($params) || is_int($params)) {
            $fieldId = $defaultEntity->getEntityKeyField() ?? 'id';
            return [$defaultEntity, [$fieldId => $params]];
        }

        return [$defaultEntity, []];
    }

    public function prepareForSave(Entity $defaultEntity, mixed $data): Entity|array|CollectionInterface
    {
        if ($data === null) {
            return $this->em->getEntity();
        }

        if (is_array($data)) {
            return $this->handleArrayData($defaultEntity, $data);
        }

        if ($data instanceof Entity) {
            $this->em->setEntity($data);
            return $data;
        }

        if ($data instanceof CollectionInterface) {
            $this->em->setEntity($data);
            return $data->all();
        }

        throw new DataAccessLayerException('Invalid data type provided for saving');
    }

    public function updateTimestamps(Entity|array|CollectionInterface $entity): void
    {
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

    public function handleArrayData(Entity $defaultEntity, array $data): Entity|array|CollectionInterface
    {
        if (empty($data)) {
            return $this->em->getEntity();
        }
        $firstItem = reset($data);
        if ($firstItem instanceof Entity) {
            $this->em->setEntity($data);
            return $data;
        }

        // Single entity data
        $this->em->setEntity($defaultEntity)->assign($data);
        return $this->em->getEntity();
    }

    private function isEntityArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        foreach ($array as $item) {
            if (!$item instanceof Entity) {
                return false;
            }
        }

        return true;
    }
}