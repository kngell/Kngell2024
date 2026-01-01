<?php

declare(strict_types=1);

class ModelOperationSave extends AbstractModelBaseOperations
{
    public function __construct(ModelUtilityInterface $utils)
    {
        return parent::__construct($utils);
    }

    public function execute(EntityManagerInterface $em, Entity $entity, mixed $data): QueryResult
    {
        $entityToSave = $this->utils->prepareForSave($entity, $data);

        if ($entityToSave === null) {
            throw new DataAccessLayerException('No valid data to save!');
        }

        $this->utils->updateTimestamps($entityToSave);
        $em->setEntity($entityToSave);
        if ($em->isEntityKeyInitialized()) {
            $result = $this->update($em, $entityToSave);
            $updateId = $entityToSave->getFieldValue($entityToSave->getEntityKeyField());
            return $result->setLastUpdateId($updateId);
        }
        return $this->insert($em, $entityToSave);
    }

    private function insert(EntityManagerInterface $em, Entity|array|CollectionInterface $entity): QueryResult
    {
        if ((is_array($entity) && ArrayUtils::isArrayList($entity)) || $entity instanceof CollectionInterface) {
            $entity = $entity[0];
        }

        $em->getRepository($entity)->create();
        return $this->getQueryResult($em);
    }

    private function update(EntityManagerInterface $em, Entity|array|CollectionInterface $entity): QueryResult
    {
        $em->getRepository($entity)->update([]);
        return $this->getQueryResult($em);
    }
}