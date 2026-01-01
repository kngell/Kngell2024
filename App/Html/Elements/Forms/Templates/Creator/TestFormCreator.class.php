<?php

declare(strict_types=1);

class TestFormCreator extends AbstractFormCreator
{
    public function __construct(private FormBuilder $formBuilder)
    {
    }

    public function create(string $action): ?FormTemplateInterface
    {
        return new TestForm($this->formBuilder);
    }
}