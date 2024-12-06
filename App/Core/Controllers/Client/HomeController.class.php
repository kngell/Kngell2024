<?php

declare(strict_types=1);

class HomeController extends Controller
{
    public function index() : string
    {
        $this->pageTitle('Home');
        // $form = $this->builder->input('text')->makeForm();
        return $this->render('index');
    }
}