<?php

declare(strict_types=1);

class AdminController extends Controller
{
    public function __construct(ProductFormCreator $frm)
    {
        $this->layout('admin');
        $this->frm = $frm;
    }

    public function index(): string
    {
        $this->pageTitle('Admin Dashboard');
        return $this->render('index');
    }

    public function login(): string
    {
        $this->pageTitle('login');
        return $this->render('login');
    }

    public function productAdd(): string
    {
        $this->pageTitle('Add Product');
        $product = new ProductPageDecorator($this, 'product/create');
        return $this->render('product-add', $product->page());
    }

    public function editProfile(): string
    {
        $this->pageTitle('Profile');
        $profile = new UserProfileDecorator($this);
        return $this->render('edit-profile', $profile->page());
    }

    public function test(): string
    {
        $this->setLayout('test');
        $this->pageTitle('Test');
        return $this->render('test');
    }

    public function productList(): string
    {
        $this->pageTitle('Product List');
        return $this->render('product-list');
    }

    public function productEdit(): string
    {
        $this->pageTitle('Edit Product');
        return $this->render('product-edit');
    }

    public function contact(): string
    {
        $this->pageTitle('Contact');
        return $this->render('contact');
    }
}