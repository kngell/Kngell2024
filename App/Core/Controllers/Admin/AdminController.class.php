<?php

declare(strict_types=1);

class AdminController extends Controller
{
    public function __construct()
    {
        $this->setLayout('admin');
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

    public function productAdd(): string
    {
        $this->pageTitle('Add Product');
        return $this->render('product-add');
    }
}