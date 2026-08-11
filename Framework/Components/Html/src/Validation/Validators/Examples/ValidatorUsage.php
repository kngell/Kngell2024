<?php

declare(strict_types=1);

// In your validators
class RequiredValidatorExample extends AbstractValidator
{
    public function validate(): string|bool
    {
        if ($this->isEmpty($this->inputValue)) {
            $message = ValidationMessageService::formatMessage('required', [$this->display]);
            $classes = ValidationMessageService::getHintClasses();
            return "<div class='" . implode(' ', $classes) . "'>" . $message . '</div>';
        }
        return false;
    }
}