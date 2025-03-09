<?php

declare(strict_types=1);

class EcommerceController extends Controller
{
    public function index() : string
    {
        $this->pageTitle('Ecommerce');
        return $this->render('index');
    }
}