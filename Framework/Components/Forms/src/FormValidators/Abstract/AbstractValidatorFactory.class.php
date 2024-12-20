<?php

declare(strict_types=1);

abstract class AbstractValidatorFactory
{
    abstract public function create(string $ruleName, string $display, mixed $inputValue, mixed $ruleValue): AbstractValidator;

    public function run(string $ruleName, string $display, mixed $inputValue, mixed $ruleValue): string|bool
    {
        $validator = $this->create($ruleName, $display, $inputValue, $ruleValue);
        return  $validator->validate();
    }
}