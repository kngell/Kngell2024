<?php

declare(strict_types=1);

class HomeController extends Controller
{
    public function __construct(private FormBuilder $builder, View $view)
    {
        parent::__construct($view);
    }

    public function index() : string
    {
        $this->pageTitle('Home');
        $form = $this->builder->add(
            $this->builder->input('text')->name('form1')
        )->makeForm();
        return $this->render('index', ['form' => $form]);
    }
}
