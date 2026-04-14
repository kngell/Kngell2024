<?php

declare(strict_types=1);

class IntegerValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
    ) {
    }

    public function validate(): array|string|bool
    {
        if ($this->isEmpty($this->inputValue)) {
            return false;
        }

        if (filter_var($this->inputValue, FILTER_VALIDATE_INT) === false) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display),
                $this->errorParams['classes'],
            );
        }

        return false;
    }
}
