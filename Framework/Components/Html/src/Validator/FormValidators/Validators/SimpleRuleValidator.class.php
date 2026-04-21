<?php

declare(strict_types=1);

final class SimpleRuleValidator extends AbstractValidator
{
    private const SIMPLE_RULES = [
        'required' => 'validateRequired',
        'numeric' => 'validateNumeric',
        'boolean' => 'validateBoolean',
    ];

    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly string $ruleName,
        private readonly mixed $ruleValue,
    ) {
    }

    public function validate(): array|string|bool
    {
        if (!isset(self::SIMPLE_RULES[$this->ruleName])) {
            return false;
        }

        $method = self::SIMPLE_RULES[$this->ruleName];
        return $this->$method();
    }

    private function validateRequired(): string|bool
    {
        if ($this->isEmpty($this->inputValue)) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display),
                $this->errorParams['classes'],
            );
        }
        return false;
    }

    private function validateNumeric(): string|bool
    {
        if (!$this->isEmpty($this->inputValue) && !is_numeric($this->inputValue)) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display),
                $this->errorParams['classes'],
            );
        }
        return false;
    }

    private function validateBoolean(): string|bool
    {
        if (!$this->isEmpty($this->inputValue) && !is_bool($this->inputValue)) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display),
                $this->errorParams['classes'],
            );
        }
        return false;
    }
}