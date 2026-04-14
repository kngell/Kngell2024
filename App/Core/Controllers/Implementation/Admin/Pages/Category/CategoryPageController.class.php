<?php

declare(strict_types=1);

class CategoryPageController extends Controller
{
    public function __construct(
        FormCreatorService $frm,
        private CategoryModel $md,
    ) {
        $this->frm = $frm;
        $this->layout('admin');
    }

    public function Add(): string
    {
        $this->pageTitle('Category');
        $hero = new CategoryFormDecorator(
            controller: $this,
            action:'category-save/index',
        );
        return $this->render('/components/category', $hero->page());
    }
}