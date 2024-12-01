<?php

declare(strict_types=1);
class HTMLBaseComponent extends AbstractHTMLComponent
{
    public function __construct()
    {
        parent::__construct();
    }

    public function display(): array
    {
        $results = [];
        $childs = $this->children->all();
        foreach ($childs as $child) {
            $results[] = $child->display();
        }
        return ArrayUtil::flatten_with_keys($results);
    }
}