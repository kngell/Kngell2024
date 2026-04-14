<?php

declare(strict_types=1);

trait ChangeDetectionTrait
{
    protected function hasActionableChanges(array $inputData, EntityManagerInterface $em): bool
    {
        if (!empty($inputData)) {
            return true;
        }
        if (!empty($em->getDirtyData())) {
            return true;
        }

        return false;
    }

    protected function getConsolidatedData(array $inputData, EntityManagerInterface $em): array
    {
        return array_merge($inputData, $em->getDirtyData());
    }

    protected function getConsolidateDataWithIds(array $inputData = []): array
    {
        $dirtyData = $this->em->getDirtyData();
        $entities = $this->em->getEntity();
        $keyField = $this->em->getEntityKeyField();

        $consolidated = [];
        foreach ($dirtyData as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $rowWithId = $row;

            if (!isset($rowWithId[$keyField]) && isset($entities[$index])) {
                $entity = $entities[$index];
                if ($entity instanceof Entity) {
                    $rowWithId[$keyField] = $entity->getEntityPrimarykeyValue();
                }
            }

            $consolidated[] = $rowWithId;
        }

        // 2. Add user input data
        foreach ($inputData as $row) {
            if (is_array($row)) {
                $consolidated[] = $row;
            }
        }

        return $consolidated;
    }
}
