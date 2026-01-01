<?php

declare(strict_types=1);

interface FormTemplateInterface
{
    public function make(string $action = '', array|Entity $formValues = [], array $formErrors = []): string;
}