<?php

// ObfuscationAwareModel.php
declare(strict_types=1);

abstract class AbstractSaveModel extends Model
{
    public function __construct(
        EntityManagerInterface $em,
        EntityFactoryInterface $factory,
        ModelContextInterface $context,
        ModelUtilityInterface $utils,
    ) {
        parent::__construct($em, $factory, $context, $utils);
    }

    public function save(null|array|Entity $data = null, array $conditions = []): QueryResult
    {
        if ($data === null) {
            throw new InvalidArgumentException('No data to save.');
        }
        $normalizedData = $this->normalizeDataForSave($data);
        $this->validateData($normalizedData);
        $normalizedData = $this->generateMissingFields($normalizedData);
        $this->saveEventData($normalizedData);
        return parent::save($normalizedData, $conditions);
    }

    abstract protected function validateData(array $data): void;

    abstract protected function generateMissingFields(array $data): array;
}