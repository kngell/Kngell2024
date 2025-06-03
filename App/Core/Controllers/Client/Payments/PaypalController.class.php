<?php

declare(strict_types=1);

class PaypalController extends Controller
{
    public function __construct(private ProductModel $productModel)
    {
        $this->currentModel($productModel);
    }

    public function product() : string
    {
        $this->pageTitle('Product');
        $products = new ProductsDecorator($this);
        $products = new UserCartItemDecorator($products);
        return $this->render('product', $products->page());
    }

    public function checkout() : string
    {
        $this->pageTitle('Checkout');
        return $this->render('checkout');
    }

    public function addToCart() : Response
    {
        $this->pageTitle('Add to Cart');
        $data = $this->request->getPost()->getAll();
        return new RedirectResponse('/paypal/cart');
    }

    public function removeFromCart($id) : Response
    {
        $this->pageTitle('Remove from Cart');
        return new RedirectResponse('/paypal/cart');
    }

    public function updateQty($id) : Response
    {
        $this->pageTitle('Update Quantity');
        return new RedirectResponse('/paypal/cart');
    }

    public function createPayment() : Response
    {
        $this->pageTitle('Create Payment');

        // Logic to create payment

        return new RedirectResponse('/paypal/product');
    }
}