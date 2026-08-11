<?php

declare(strict_types=1);

class CustomRuleValidator extends AbstractValidator
{
    public function __construct(
        private readonly string $ruleName,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue,
        private readonly AbstractValidatorCreator $creator,
    ) {
    }

    public function validate(): array|string|bool
    {
        return $this->creator->executeCustomRule(
            $this->ruleName,
            $this->display,
            $this->inputValue,
            $this->ruleValue,
        );
    }
}