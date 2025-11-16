<?php

declare(strict_types=1);

class UniqueValidator extends AbstractValidator
{
    private const string IDENTIFIER_FIELD = 'public_id';

    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly ?string $ruleValue,
        private readonly Model $md,
        private readonly ?array $formData,
        private string $fieldName,
    ) {
    }

    public function validate(): array|string|bool
    {
        if ($this->isEmpty($this->inputValue)) {
            return false;
        }

        $ignoreCurrent = false;
        if ($this->ruleValue === 'ignore_current') {
            $ignoreCurrent = $this->shouldIgnoreCurrent();
        }

        $conditions = [$this->fieldName, $this->inputValue];
        $existingRecord = $this->md->one($conditions);

        if ($existingRecord->exists() && !$ignoreCurrent) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display),
                $this->errorParams['classes'],
            );
        }

        return false;
    }

    private function shouldIgnoreCurrent(): bool
    {
        if ($this->ruleValue !== 'ignore_current' || $this->formData === null) {
            return false;
        }

        $currentIdValue = $this->formData[self::IDENTIFIER_FIELD] ?? null;

        if (empty($currentIdValue)) {
            return false;
        }

        $conditions = [$this->fieldName, $this->inputValue];
        $existingRecord = $this->md->one($conditions);

        if ($existingRecord->exists()) {
            $existingId = $existingRecord->getFieldValue(self::IDENTIFIER_FIELD);

            if ($existingId == $currentIdValue) {
                return true;
            }
        }
        return false;
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === '[]') {
            return true;
        }
        if (is_array($value) && empty($value)) {
            return true;
        }
        if (is_string($value) && trim($value) === '') {
            return true;
        }
        return false;
    }
}