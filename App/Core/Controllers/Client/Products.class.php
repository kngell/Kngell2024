<?php

declare(strict_types=1);
#[ControllerAttr]
class Products extends BaseController
{
    private Product $product;

    public function __construct(BaseView $view, Product $product)
    {
        parent::__construct($view);
        $this->product = $product;
    }

    public function index() : string
    {
        $products = $this->product->getData();
        $this->pageTitle('Products');
        return $this->render('products/index', [
            'products' => $products,
        ]);
    }

    public function show(string $id) : string
    {
        $this->pageTitle('Show');
        return $this->render('show', ['id' => $id]);
    }

    public function showPage(string $title, string $id, string $page)
    {
        dump($title, $id, $page);

        return $title;
    }
}