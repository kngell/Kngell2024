<?php

declare(strict_types=1);

abstract class AbstractFormCreator
{
    abstract public function create(string $action): ?FormTemplateInterface;

    public function make(string $action = '', array|Entity $formValues = [], array $formErrors = [], array $files = []): string
    {
        $form = $this->create($action);
        if (null !== $form) {
            return $form->make($action, $formValues, $formErrors, $files);
        }
        return '';
    }
}