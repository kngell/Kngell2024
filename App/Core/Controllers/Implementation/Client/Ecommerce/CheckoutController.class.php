<?php

declare(strict_types=1);

class CheckoutController extends Controller
{
    public function __construct(private CartModel $cartModel)
    {
        $this->setLayout('ecommerce');
    }

    public function index(): string
    {
        $this->pageTitle('Checkout');
        // dd($this->cartModel->getTableColumns('transactions'));
        // $cart = new UserCartItemDecorator($this);
        return $this->render('index'); // $cart->page()
    }
}