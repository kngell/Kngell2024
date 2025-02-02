<?php

declare(strict_types=1);

class DashboardController extends Controller
{
    // public function __construct()
    // {
    //     $this->handleCors();
    // }

    public function index() : string
    {
        $this->pageTitle('Ease Dashboard');
        $this->setLayout('admin');
        return $this->render('index');
    }

    // private function handleCors(): void
    // {
    //     header('Access-Control-Allow-Origin: *');
    //     header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    //     header('Access-Control-Allow-Headers: Content-Type, Authorization');
    // }
}