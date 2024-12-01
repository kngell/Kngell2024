<?php

declare(strict_types=1);
class Menu extends AbstractHTMLComponent
{
    public function __construct(?string $navbarTemplate = null)
    {
        parent::__construct($navbarTemplate);
    }

    public function display(): string
    {
        return '';
    }
}