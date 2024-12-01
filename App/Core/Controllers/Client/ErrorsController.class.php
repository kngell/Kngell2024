<?php

declare(strict_types=1);
class ErrorsController extends Controller
{
    public function index(array $data) : String
    {
        $this->pageTitle('Errors');
        $this->setLayout('default');
        return $this->render('errors' . DS . '404', $data);
    }

    public function _500(array $data) : String
    {
        $this->pageTitle('Errors');
        $this->setLayout('default');
        return $this->render('errors' . DS . '500', $data);
    }
}