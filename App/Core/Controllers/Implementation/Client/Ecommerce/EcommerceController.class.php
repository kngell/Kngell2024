<?php

declare(strict_types=1);

class EcommerceController extends Controller
{
    public function __construct(
    ) {
        $this->layout('ecommerce');
    }

    public function index(): string
    {
        $this->pageTitle('Ecommerce');
        $decoratedPage = $this->decorate(ClientHeaderDecorator::class, $this);
        $decoratedPage = $this->decorate(IndexPageDecorator::class, $decoratedPage);
        return $this->render('index', $decoratedPage->page());
    }

    public function shop(): string
    {
        $this->pageTitle('Shop');
        return $this->render('shop');
    }

    public function product(): string
    {
        $this->pageTitle('Product');
        return $this->render('product');
    }

    public function shoppingCart(): string
    {
        $this->pageTitle('Shopping Cart');
        return $this->render('shopping-cart');
    }
}