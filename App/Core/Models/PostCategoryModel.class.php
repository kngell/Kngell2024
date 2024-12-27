<?php

declare(strict_types=1);

class PostCategoryModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function test() : array
    {
        $postcategory = $this->all();
        return $postcategory->getResults('class')->all();
    }
}