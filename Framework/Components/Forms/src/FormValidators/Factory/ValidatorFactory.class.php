<?php

declare(strict_types=1);

class ValidatorFactory extends AbstractValidatorFactory
{
    public function __construct(private ?Model $model)
    {
    }

    public function create(string $ruleName, string $display, mixed $inputValue, mixed $ruleValue) : AbstractValidator
    {
        return match ($ruleName) {
            'required' => new RequiredValidator($display, $inputValue, $ruleValue),
            'min' => new MinValidator($display, $inputValue, $ruleValue),
            'max' => new MaxValidator($display, $inputValue, $ruleValue),
            'valid_email' => new EmailValidator($display, $inputValue, $ruleValue),
            'unique' => new UniqueValidator($display, $inputValue, $ruleValue, $this->model)
        };
    }
}