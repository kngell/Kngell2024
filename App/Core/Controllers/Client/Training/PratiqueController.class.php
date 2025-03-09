<?php

declare(strict_types=1);
class PratiqueController extends Controller
{
    public function index() : string
    {
        $this->pageTitle('Exos Pratiques');
        $this->setLayout('training');
        return $this->render('index');
    }
}