<?php

declare(strict_types=1);
class Training1Controller extends Controller
{
    public function index() : string
    {
        $this->pageTitle('Restaurant Georgia');
        $this->setLayout('training');
        return $this->render('index');
    }
}