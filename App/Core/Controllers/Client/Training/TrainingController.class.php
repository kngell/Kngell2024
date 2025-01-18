<?php

declare(strict_types=1);
class TrainingController extends Controller
{
    public function index() : string
    {
        $this->pageTitle('Natours | Exciting tours for adventurous people');
        $this->setLayout('training');
        return $this->render('index');
    }
}