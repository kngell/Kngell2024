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

    public function _500() : String
    {
        $this->pageTitle('Errors');
        $this->setLayout('default');
        return $this->render('500');
    }

    public function _400() : string
    {
        $this->pageTitle('Errors');
        $this->setLayout('default');
        return $this->render('404');
    }
}