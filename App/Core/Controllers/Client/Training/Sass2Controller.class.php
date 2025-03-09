<?php

declare(strict_types=1);
class Sass2Controller extends Controller
{
    public function index() : string
    {
        $this->pageTitle('Natours | Exciting tours for adventurous people');
        $this->setLayout('training');
        return $this->render('index');
    }

    public function floatLayout() : string
    {
        $this->pageTitle('Natours | Float Layout');
        $this->setLayout('training');
        return $this->render('float-layout');
    }
}