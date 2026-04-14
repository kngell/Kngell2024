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
        $model = $this->md;
        $column = $this->extractFieldName($this->fieldName);
        $ruleType = $this->ruleValue;

        if (is_array($this->ruleValue)) {
            $modelName = ucfirst(StringUtils::snakeCaseToCamelCase($this->ruleValue['modelName'])) . 'Model';
            $column = $this->ruleValue['fieldNames'][0] ?? $this->fieldName;
            $ruleType = $this->ruleValue['ignore'] ?? 'ignore_current';

            $model = App::diGet($modelName);
        }
        // dd($this->formData);
        $ignoreCurrent = $this->shouldIgnoreCurrent();

        $conditions = [$column => $this->inputValue];
        $existingRecord = $model->one($conditions);

        if ($existingRecord->exists()) {
            if ($ignoreCurrent) {
                $existingRecord = $existingRecord->asClass();
                $isSameRecord = $this->isSameRecord($existingRecord, $model);
                if (!$isSameRecord) {
                    return $this->buildErrorMessage($this->errorParams);
                }
                return false;
            }

            [$message,$classes] = $this->buildErrorMessage($this->errorParams);
            return $this->errorMessage(
                sprintf($message, $this->display),
                $classes,
            );
        }

        return false;
    }

    private function shouldIgnoreCurrent(): bool
    {
        $ruleValue = $this->ruleValue;
        if (is_array($ruleValue)) {
            $ruleValue = $ruleValue['ignore'];
        }
        if ($ruleValue !== 'ignore_current') {
            return false;
        }

        if ($this->formData === null || empty($this->formData)) {
            return false;
        }

        return true;
    }

    private function isSameRecord(Entity $existing, Model $model): bool
    {
        $entity = $model->getEntityManager()->getEntity();

        if ($existing->entityKeyIsInitialzed()) {
            $existingId = $existing->getEntityPrimarykeyValue();
        } else {
            $existingId = null;
        }
        $pkField = $entity->getEntityKeyField() ?? 'id';
        // $pkProperty = $entity->getEntityKeyProperty();
        // $existingId = $existing->getFieldValue($pkField);
        $currentId = null;

        if ($this->fieldName && strpos($this->fieldName, '[') !== false) {
            $currentId = $this->getIdFromNestedPath($pkField);
        } else {
            $currentId = $this->formData['pdt_id'] ?? $this->getCurrentIdFromFormData($pkField, 'id');
        }

        if ($currentId === null || $existingId === null) {
            return false;
        }
        return (string) $existingId === (string) $currentId;
    }

    private function getIdFromNestedPath(string $pkField): mixed
    {
        preg_match_all('/[^[\]]+/', $this->fieldName, $matches);
        $keys = $matches[0];
        array_pop($keys);

        $cursor = $this->formData;
        foreach ($keys as $key) {
            if (!isset($cursor[$key])) {
                return null;
            }
            $cursor = $cursor[$key];
        }
        return $cursor[$pkField] ?? $cursor['id'] ?? null;
    }

    private function getCurrentIdFromFormData(string $field, string $property): mixed
    {
        if ($this->formData === null) {
            return null;
        }
        if (isset($this->formData[$field])) {
            return $this->formData[$field];
        }
        if (isset($this->formData[$property])) {
            return $this->formData[$property];
        }
        foreach (['id', 'ID', 'Id'] as $key) {
            if (isset($this->formData[$key])) {
                return $this->formData[$key];
            }
        }

        return null;
    }
}
