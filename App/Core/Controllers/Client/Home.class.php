<?php

declare(strict_types=1);
class Home extends BaseController
{
    public function index() : string
    {
        $this->pageTitle('Home');
        return $this->render('index');
    }
}