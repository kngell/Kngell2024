<?php

declare(strict_types=1);
class ErrorsController extends Controller
{
    public function __construct()
    {
        $this->layout('admin');
    }

    public function index(array $data): String
    {
        $this->pageTitle('Errors');
        return $this->render('dev', $data);
    }

    public function e500(): String
    {
        $this->pageTitle('Errors');
        return $this->render('500');
    }

    public function e404(): string
    {
        $this->pageTitle('Errors');
        return $this->render('404');
    }

    public function clientError(): string
    {
        $this->pageTitle('Errors');
        return $this->render('client-error');
    }

    public function restrictAccess(): string
    {
        $this->pageTitle('Errors');
        return $this->render('restricted');
    }
}