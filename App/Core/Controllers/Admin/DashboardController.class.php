<?php

declare(strict_types=1);

class DashboardController extends Controller
{
    public function __construct()
    {
        // $this->setLayout('admin');
        // $this->handleCors();
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

    // private function handleCors(): void
    // {
    //     header('Access-Control-Allow-Origin: *');
    //     header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    //     header('Access-Control-Allow-Headers: Content-Type, Authorization');
    // }
    private function active(string $function) : array
    {
        return [$function . 'Active' => 'k-active'];
    }
}