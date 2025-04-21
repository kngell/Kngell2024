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
        return $this->render('index', $this->active(__FUNCTION__));
    }

    public function ecommerce() : string
    {
        $this->pageTitle('Ecommerce Dashboard');
        return $this->render('ecommerce', $this->active(__FUNCTION__));
    }

    private function active(string $function) : array
    {
        return [$function => 'k-active'];
    }
}