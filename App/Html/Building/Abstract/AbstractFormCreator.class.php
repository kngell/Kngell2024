<?php

declare(strict_types=1);

abstract class AbstractFormCreator
{
    protected ?FormConfig $formConfig = null;

    abstract public function create(): ?FormTemplateInterface;

    public function make(string $action = '', array|Entity $formValues = [], array $formErrors = [], array $files = []): string
    {
        $form = $this->create();
        if (null !== $form) {
            return $form->make($action, $formValues, $formErrors, $files);
        }
        return '';
    }

    /**
     * @param null|FormConfig $formConfig
     *
     * @return AbstractFormCreator
     */
    public function setFormConfig(?FormConfig $formConfig): AbstractFormCreator
    {
        $this->formConfig = $formConfig;
        return $this;
    }
}