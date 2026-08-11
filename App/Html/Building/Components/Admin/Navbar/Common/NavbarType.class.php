<?php

declare(strict_types=1);

enum NavbarType: string
{
    public function getDecoratorClass(): string
    {
        return match ($this) {
            self::DEFAULT,   self::ECOMMERCE => DefaultNavbarDecorator::class,
            self::ADMIN => AdminNavbarDecorator::class,
        };
    }
    case DEFAULT = 'default';
    case ADMIN = 'admin';
    case COMPACT = 'compact';
    case MINIMAL = 'minimal';
    case ECOMMERCE = 'ecommerce';
}