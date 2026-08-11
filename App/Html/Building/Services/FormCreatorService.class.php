<?php

declare(strict_types=1);
class FormCreatorService extends AbstractFormCreator
{
    public function __construct(
        private FormFactory $factory,
    ) {
    }

    public function create(): ?FormTemplateInterface
    {
        return $this->factory->getForm($this->formConfig);
    }
}