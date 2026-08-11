<?php

declare(strict_types=1);

enum HeroSectionLinks: string
{
    case ADD = '/admin/hero-section-save/index';
    case CONFIRM_DELETE = '/admin/hero-confirm-deletion/confirm';
    case REDIRECT = '/admin/hero-list/index';
}