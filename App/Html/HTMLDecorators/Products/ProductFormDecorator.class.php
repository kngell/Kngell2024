<?php

declare(strict_types=1);

class ProductFormDecorator extends AbstractHtmlDecorator
{
    public function __construct(
        Controller $controller,
        private string $action,
        private array|Entity $formValues = [],
        private array $formErrors = [],
        private array $files = [],
    ) {
        parent::__construct($controller);
    }

    public function page(): array
    {
        $frm = $this->form($this->action, $this->formValues, $this->formErrors, $this->files);
        return ['product_form' => $frm];
    }
}