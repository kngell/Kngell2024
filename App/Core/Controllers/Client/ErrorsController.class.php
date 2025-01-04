<?php

declare(strict_types=1);
class ErrorsController extends Controller
{
    public function index(array $data) : String
    {
        $this->pageTitle('Errors');
        $this->setLayout('default');
        return $this->render('errors' . DS . 'dev', $data);
    }

    public function e500() : String
    {
        $this->pageTitle('Errors');
        $this->setLayout('default');
        return $this->render('500');
    }

    public function e400() : string
    {
        $this->pageTitle('Errors');
        $this->setLayout('default');
        return $this->render('404');
    }

    public function restrictAccess() : string
    {
        $this->pageTitle('Errors');
        $this->setLayout('default');
        return $this->render('restricted');
    }
}