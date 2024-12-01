<?php

declare(strict_types=1);
class SearchBox extends AbstractHTMLComponent
{
    public function __construct(NavigationPath $paths)
    {
        parent::__construct($paths);
    }

    public function display(): string
    {
        return '';
    }
}