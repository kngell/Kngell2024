<?php

declare(strict_types=1);

interface ValidatorInterface
{
    public function validate(array $inputFields, string $rules, ?Model $model = null): ValidationResult;
}