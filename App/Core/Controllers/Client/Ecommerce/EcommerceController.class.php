<?php

declare(strict_types=1);

class EcommerceController extends Controller
{
    public function __construct()
    {
        $this->setLayout('ecommerce');
    }

    public function index(): string
    {
        $this->pageTitle('Ecommerce');
        return $this->render('index');
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