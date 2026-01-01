<?php

declare(strict_types=1);

class UniqueValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly ?string $ruleValue,
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

        $ignoreCurrent = $this->shouldIgnoreCurrent();

        $conditions = [$this->fieldName => $this->inputValue];
        $existingRecord = $this->md->one($conditions);

        if ($existingRecord->exists()) {
            if ($ignoreCurrent) {
                $existingRecord = $existingRecord->asClass();
                $isSameRecord = $this->isSameRecord($existingRecord);
                if (!$isSameRecord) {
                    return $this->buildErrorMessage();
                }
                return false;
            }

            [$message,$classes] = $this->buildErrorMessage();
            return $this->errorMessage(
                sprintf($message, $this->display),
                $classes,
            );
        }

        return false;
    }

    private function shouldIgnoreCurrent(): bool
    {
        if ($this->ruleValue !== 'ignore_current') {
            return false;
        }

        if ($this->formData === null || empty($this->formData)) {
            return false;
        }

        return true;
    }

    private function isSameRecord(Entity $existingRecord): bool
    {
        $entity = $this->md->getEntityManager()->getEntity();
        $primaryKeyField = $entity->getEntityKeyField() ?? 'id';
        $primaryKeyProperty = $entity->getEntityKeyProperty() ?? 'id';

        $currentId = $this->getCurrentIdFromFormData($primaryKeyField, $primaryKeyProperty);

        if ($currentId === null) {
            return false;
        }

        $existingId = $existingRecord->getFieldValue($primaryKeyField)
                    ?? $existingRecord->getFieldValue($primaryKeyProperty);

        if ($existingId === null) {
            return false;
        }
        return $existingId == $currentId;
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

    private function buildErrorMessage(): array
    {
        $message = $this->errorParams['message'] ?? '%s must be unique';
        $classes = $this->errorParams['classes'] ?? ['text-danger', 'validation-error'];

        return [$message, $classes];
    }
}