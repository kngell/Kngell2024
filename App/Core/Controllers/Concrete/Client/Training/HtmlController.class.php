<?php

declare(strict_types=1);
class HtmlController extends Controller
{
    public function index() : string
    {
        $this->pageTitle('Html Course');
        $this->setLayout('training');
        return $this->render('index');
    }
}