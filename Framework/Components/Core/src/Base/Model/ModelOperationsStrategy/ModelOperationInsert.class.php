<?php

declare(strict_types=1);

class ModelOperationInsert extends AbstractModelBaseOperations
{
    public function execute(EntityManagerInterface $em, Entity $prototype, mixed $data): QueryResult
    {
        $payload = $data instanceof ModelOperationPayload
            ? $data
            : $this->utils->normalizeData($data, $prototype);

        if ($payload->isCollection()) {
            $collection = new Collection();
            $itemsToInsert = $payload->hasInserts() ? $payload->getInserts() : $payload->getData();

            foreach ($itemsToInsert as $item) {
                if ($item instanceof Entity) {
                    $this->utils->updateTimestamps($item);
                    $collection->add($item);
                } elseif (ArrayUtils::isAssoc($item)) {
                    $entity = clone $prototype;
                    $entity->assign($item);
                    $this->utils->updateTimestamps($entity);
                    $collection->add($entity);
                }
            }
            $data = $collection;
        } else {
            $data = $payload->getData();
            if ($data instanceof Entity) {
                $entity = $data;
            } elseif (is_array($data) && ArrayUtils::isAssoc($data)) {
                $entity = clone $prototype;
                $entity->assign($data);
            } else {
                throw new DataAccessLayerException('Invalid data type');
            }
            // dd($entity);
            $this->utils->updateTimestamps($entity);
            $data = $entity;
        }

        $em->setEntity($data)->getRepository($prototype)->create();
        return $this->getQueryResult($em);
    }
}