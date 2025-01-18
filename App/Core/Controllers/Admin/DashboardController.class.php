<?php

declare(strict_types=1);

class DashboardController extends Controller
{
    public function index() : string
    {
        $this->pageTitle('Ease Dashboard');
        $this->setLayout('admin');
        return $this->render('index');
    }
}