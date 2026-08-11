<?php

declare(strict_types=1);

class UniqueValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly null|string|array $ruleValue,
        private readonly Model $md,
        private readonly ?array $formData,
        private string $fieldName,
    ) {
        parent::__construct();
    }

    public function validate(): array|string|bool
    {
        if ($this->isEmpty($this->inputValue)) {
            return false;
        }

        $model = $this->getModel();
        $column = $this->getColumnName();
        $ignoreCurrent = $this->shouldIgnoreCurrent();

        $conditions = [$column => $this->inputValue];
        $existingRecord = $model->one($conditions);

        if ($existingRecord->exists()) {
            if ($ignoreCurrent) {
                $existingEntity = $existingRecord->asClass();
                if ($this->isSameRecord($existingEntity, $model)) {
                    return false;
                }
            }

            [$message, $classes] = $this->buildErrorMessage($this->errorParams);
            return $this->errorMessage(
                sprintf($message, $this->display),
                $classes,
            );
        }

        return false;
    }

    private function getModel(): Model
    {
        if (is_array($this->ruleValue) && isset($this->ruleValue['modelName'])) {
            $modelName = ucfirst(StringUtils::snakeCaseToCamelCase($this->ruleValue['modelName'])) . 'Model';
            return App::diGet($modelName);
        }
        return $this->md;
    }

    private function getColumnName(): string
    {
        if (is_array($this->ruleValue) && isset($this->ruleValue['fieldNames'][0])) {
            return $this->ruleValue['fieldNames'][0];
        }
        return $this->extractFieldName($this->fieldName);
    }

    private function shouldIgnoreCurrent(): bool
    {
        if (is_array($this->ruleValue)) {
            $ignore = $this->ruleValue['ignore'] ?? null;
        } else {
            $ignore = $this->ruleValue;
        }

        return $ignore === 'ignore_current' && !empty($this->formData);
    }

    private function isSameRecord(Entity $existing, Model $model): bool
    {
        $existingId = $this->getExistingRecordId($existing);
        $currentId = $this->getCurrentRecordId($model);

        if ($existingId === null || $currentId === null) {
            return false;
        }

        return (string) $existingId === (string) $currentId;
    }

    private function getExistingRecordId(Entity $existing): mixed
    {
        return $existing->entityKeyIsInitialzed()
            ? $existing->getEntityPrimarykeyValue()
            : null;
    }

    private function getCurrentRecordId(Model $model): mixed
    {
        $entity = $model->getEntityManager()->getEntity();
        $pkField = $entity->getEntityKeyField();

        $rawId = $this->extractIdFromFormData($pkField);

        if ($rawId === null) {
            return null;
        }

        $payload = ModelQueryPayload::create($entity, [$pkField => $rawId]);
        $conditions = $payload->getConditions();

        return $conditions[$pkField] ?? null;
    }

    private function extractIdFromFormData(string $pkField): mixed
    {
        if ($this->formData === null) {
            return null;
        }

        // Try direct access first
        if (isset($this->formData[$pkField])) {
            return $this->formData[$pkField];
        }

        // Try nested path (e.g., form[entity][id])
        $id = $this->extractIdFromNestedPath($pkField);
        if ($id !== null) {
            return $id;
        }

        // Try common ID field names
        foreach (['id', 'ID', 'Id'] as $key) {
            if (isset($this->formData[$key])) {
                return $this->formData[$key];
            }
        }

        return null;
    }

    private function extractIdFromNestedPath(string $pkField): mixed
    {
        if (!str_contains($this->fieldName, '[')) {
            return null;
        }

        // Extract keys from field name like "form[entity][id]"
        preg_match_all('/[^[\]]+/', $this->fieldName, $matches);
        $keys = $matches[0];
        array_pop($keys); // Remove the last key (the field itself)

        $cursor = $this->formData;
        foreach ($keys as $key) {
            if (!isset($cursor[$key]) || !is_array($cursor[$key])) {
                return null;
            }
            $cursor = $cursor[$key];
        }

        // Try to find the ID in the nested data
        return $cursor[$pkField] ?? $cursor['id'] ?? null;
    }
}