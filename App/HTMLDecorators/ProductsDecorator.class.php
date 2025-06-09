<?php

declare(strict_types=1);

class ProductsDecorator extends AbstractHtmlDecorator
{
    public function __construct(Controller $controller)
    {
        parent::__construct($controller);
    }

    public function page(): array
    {
        /** @var ProductModel */
        $model = $this->getModel(ProductModel::class);
        $products = $model->getProducts();
        $productList = new ProductsHtmlElement($products, $this->builder);
        return array_merge(
            $this->controller->page(),
            ['products' => $productList->display()]
        );
    }
}