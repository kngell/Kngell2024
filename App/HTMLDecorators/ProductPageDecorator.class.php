<?php

declare(strict_types=1);

class ProductPageDecorator extends AbstractHtmlDecorator
{
    private string $action;

    public function __construct(Controller $controller, string $action)
    {
        parent::__construct($controller);
        $this->action = $action;
    }

    public function page(): array
    {
        $frm = $this->form($this->action);
        return ['product_form' => $frm];
    }
}