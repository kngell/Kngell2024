<?php

declare(strict_types=1);
class PostCategoryController extends Controller
{
    public function __construct(private PostCategoryModel $postCategory)
    {
    }

    public function index() : string
    {
        dump($this->postCategory->getTableColumns('category'));
        return $this->render('index');
    }
}