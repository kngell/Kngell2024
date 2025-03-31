<?php

declare(strict_types=1);
class PratiqueController extends Controller
{
    public function __construct()
    {
        $this->setLayout('training');
    }

    public function index() : string
    {
        $this->pageTitle('Exos Pratiques');
        return $this->render('index');
    }

    public function figma() : string
    {
        $this->pageTitle('Exos Pratiques');

        return $this->render('figma');
    }
}