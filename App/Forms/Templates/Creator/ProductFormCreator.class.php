<?php

declare(strict_types=1);

class ProductFormCreator extends AbstractFormCreator
{
    public function __construct(private FormBuilder $formBuilder)
    {
    }

    public function create(string $action): ?FormTemplateInterface
    {
        return match (true) {
            StringUtils::strContainsArrayItem($action, ['edit', 'new', 'update', 'create', 'destroy']) => new ProductForm($this->formBuilder),
            $action === 'delete' => new ProductFormConfirmation($this->formBuilder),
            default => null,
        };
    }
}