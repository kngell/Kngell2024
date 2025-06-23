<?php

declare(strict_types=1);

abstract class AbstractValidator
{
    protected array $class = ['invalid-feedback'];

    abstract public function validate() : string|bool;

    protected function erroMessage(string $errMsg) : string
    {
        $errMsg = nl2br(htmlspecialchars($errMsg));
        return "<div class='" . implode(' ', $this->class) . "'>" . $errMsg . '</div>';
    }
}