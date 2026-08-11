<?php

declare(strict_types=1);

class MaxItemsValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly int $ruleValue,
    ) {
    }

    public function validate(): array|string|bool
    {
        if (is_array($this->inputValue) && count($this->inputValue) > $this->ruleValue) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display, $this->ruleValue),
                $this->errorParams['classes'],
            );
        }

        return false;
    }
}