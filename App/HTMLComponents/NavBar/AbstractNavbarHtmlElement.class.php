<?php

declare(strict_types=1);
abstract class AbstractNavbarHtmlElement implements HtmlComponentsInterface
{
    protected const array NAVBAR_CLASS = [
        'navbar',
        'navbar-expand-lg',
        'navbar-light',
    ];
    protected const array NAVBAR_STYLE = ['background-color: #e3f2fd'];

    protected const array BTN_CUSTOM = [
        'data-bs-toggle' => 'collapse',
        'data-bs-target' => '#navbarSupportedContent',
        'aria-controls' => 'navbarSupportedContent',
        'aria-expanded' => 'false',
        'aria-label' => 'Toggle navigation',
    ];
}