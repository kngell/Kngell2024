<?php

declare(strict_types=1);

class HeroListController extends Controller
{
    public function __construct()
    {
        $this->layout('admin');
    }

    public function index(): string
    {
        $this->pageTitle('Hero Section List');
        $list = $this->decorate(HeroListDecorator::class, $this, []);
        return $this->render('/components/table-list', $list->page());
    }
}