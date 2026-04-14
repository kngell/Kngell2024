<?php

// ObfuscationAwareModel.php
declare(strict_types=1);

abstract class AbstractSaveModel extends Model
{
    protected ObfuscationService $obfuscationService;

    public function __construct(
        EntityManagerInterface $em,
        EntityFactoryInterface $factory,
        ModelContextInterface $context,
        ModelUtilityInterface $utils,
        ObfuscationService $obfuscationService,
    ) {
        parent::__construct($em, $factory, $context, $utils);
        $this->obfuscationService = $obfuscationService;
    }

    public function save(null|array|Entity $data = null, array $conditions = []): QueryResult
    {
        if ($data === null) {
            throw new InvalidArgumentException('No data to save.');
        }
        $normalizedData = $this->normalizeDataForSave($data);
        $normalizedData = $this->deobfuscateData($normalizedData);
        $this->validateData($normalizedData);
        $normalizedData = $this->generateMissingFields($normalizedData);
        $keyField = $this->entity->getEntityKeyField();

        if (isset($normalizedData[$keyField]) && !empty($normalizedData[$keyField])) {
            $this->saveEventData($normalizedData[$keyField], $keyField);
        }

        return parent::save($normalizedData, $conditions);
    }

    protected function deobfuscateData(array $data): array
    {
        return $this->obfuscationService->prepareForSave($data, $this->entityClassName);
    }

    protected function resolveObfuscatedId(?string $obfuscatedId): ?int
    {
        if ($obfuscatedId === null) {
            return null;
        }
        return $this->obfuscationService->deobfuscateId($obfuscatedId, $this->entityClassName);
    }

    abstract protected function validateData(array $data): void;

    abstract protected function generateMissingFields(array $data): array;
}