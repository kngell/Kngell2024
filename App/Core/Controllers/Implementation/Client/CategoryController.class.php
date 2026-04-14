<?php

declare(strict_types=1);

class CategoryController extends Controller
{
    public function __construct(private CategoryModel $category)
    {
    }

    public function index() : string
    {
        $this->category->getId();
        return $this->render('index');
    }
}
