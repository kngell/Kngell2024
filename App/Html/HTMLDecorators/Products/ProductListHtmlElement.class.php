<?php

declare(strict_types=1);

class ProductListHtmlElement extends AbstractHtml
{
    /** @var ProductShow[] */
    private array $products;

    private HtmlBuilder $builder;

    /**
     * @param array $products
     * @param HtmlBuilder $builder
     *
     * @return void
     */
    public function __construct(array $products, HtmlBuilder $builder)
    {
        $this->products = $products;
        $this->builder = $builder;
    }

    public function display(): string
    {
        $html = $this->builder;
        $productsHtml = [];
        /** @var ProductShow $product */
        foreach ($this->products as $product) {
        }
        return implode(' ', $productsHtml);
    }
}