<?php

declare(strict_types=1);

abstract class AbstractValidator
{
    abstract public function validate(): array|string|bool;

    protected function errorMessage(string $errMsg, array $class): string
    {
        // $errMsg = nl2br(htmlspecialchars($errMsg));
        return "<small class='" . implode(' ', $class) . "'>" . $errMsg . '</small>';
    }
}