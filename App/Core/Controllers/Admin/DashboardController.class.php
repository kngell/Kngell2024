<?php

declare(strict_types=1);

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->setLayout('admin');
    }

    public function index() : string
    {
        $this->pageTitle('Dashboard');
        return $this->render('index');
    }
}