<?php

declare(strict_types=1);

class NavbarDecorator extends WebElementDecorator
{
    public function page(): string
    {
        return parent::page();
    }
}
