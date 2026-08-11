<?php

declare(strict_types=1);
class Sass2Controller extends Controller
{
    public function __construct()
    {
        $this->setLayout('training');
    }

    public function index() : string
    {
        $this->pageTitle('Natours | Exciting tours for adventurous people');
        return $this->render('index');
    }

    public function floatLayout() : string
    {
        $this->pageTitle('Natours | Float Layout');
        return $this->render('float-layout');
    }

    public function flexbox() : string
    {
        $this->pageTitle('Trillo &mdash; Your on in one booking app');
        return $this->render('flexbox');
    }
}