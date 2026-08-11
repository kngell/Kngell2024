<?php

declare(strict_types=1);

enum DeletionLabel: string
{
    public function getLabel(?BlockType $type = null): string
    {
        if ($this === self::CONTENT_BLOCK) {
            return $type->getPageTitle();
        }

        return $this->value;
    }
    case HERO = 'Hero Section';
    case SMALL_BANNER = 'Small Banner';
    case BIG_BANNER = 'Big Banner';
    case PRODUCT = 'product';
    case CATEGORY = 'Category Section';
    case CONTENT_BLOCK = 'Content Block';
    case FOOTER_MENU_COLUMN = 'Footer Menu';
    case FOOTER_MENU_LINK = 'footer Menu link';
    case FOOTER_ABOUT = 'footer about';
    case FOOTER_SOCIAL = 'footer social';
}