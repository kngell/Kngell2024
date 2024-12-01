<?php

declare(strict_types=1);
class ShoppingCartHTMLComponent extends AbstractHTMLComponent
{
    protected array $taxesProducts = [];
    private string $html = '';
    private string $section = 'shoppingCart';

    public function __construct(array $params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        $childs = $this->children->all();
        foreach ($childs as $child) {
            ! $child instanceof self ? $child->setTaxesProducts($this->taxesProducts) : '';
            [$property,$html,$this->taxesProducts] = $child->display();
            $this->html = match (true) {
                $property == 'shoppingCartItems' => $this->shoppingCartItems($html),
                $property == 'shoppingCartSubtotal' => $this->shoppingCartSubtotal($html),
            };
        }
        return [$this->section => $this->html];
    }

    private function shoppingCartItems(string $html) : string
    {
        $this->section = 'shoppingCart';
        return str_replace('{{shopping_cart_items}}', $html, $this->getTemplate('shoppingCartPath'));
    }

    private function shoppingCartSubtotal(string $html) : string
    {
        $this->section = 'shoppingCart';
        return str_replace('{{shopping_cart_subTotal}}', $html, $this->html);
    }
}