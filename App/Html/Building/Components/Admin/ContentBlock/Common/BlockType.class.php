<?php

declare(strict_types=1);

enum BlockType: string
{
    public function getPositionEnum(): string
    {
        return match ($this) {
            self::SMALL_BANNER => SmallBannerPosition::class,
            self::BIG_BANNER => BigBannerPosition::class,
        };
    }

    public function getAllValues(): array
    {
        return match ($this) {
            self::SMALL_BANNER => SmallBannerPosition::getAllValues(),
            self::BIG_BANNER => BigBannerPosition::getAllValues(),
        };
    }

    public function getView(): ?string
    {
        return match($this) {
            self::HERO,
            self::SMALL_BANNER,
            self::BIG_BANNER,
            self::BANNER_SQUARE,
            self::BANNER_LEFT_WIDE,
            self::DISCOUNT_ROW,
            self::SUMMER_BANNER => '/components/main_form',
        };
    }

    public function getEntityKey(): string
    {
        return $this->value;
    }

    public function getEntityPluralName(): string
    {
        return match($this) {
            self::HERO => 'heroes',
            self::SMALL_BANNER => 'small_banners',
            self::BIG_BANNER => 'big_banners',
            self::SUMMER_BANNER => 'summer_banners',
            self::BANNER_SQUARE => 'banner_squares',
            self::BANNER_LEFT_WIDE => 'banner_left_wides',
            self::DISCOUNT_ROW => 'discount_rows',
        };
    }

    public function getMetadataKey(): string
    {
        return $this->value;
    }

    public function getFormRules(): string
    {
        return match ($this) {
            self::SMALL_BANNER => 'smallBannerRules',
            self::BIG_BANNER => 'bigBannerRules'
        };
    }

    public function getPageTitle(): ?string
    {
        return match($this) {
            self::HERO => 'Hero Section',
            self::SMALL_BANNER => 'Small Banner',
            self::SUMMER_BANNER => 'Summer Banner',
            self::BIG_BANNER => 'Big Banner',
            self::BANNER_SQUARE => 'Banner Square',
            self::BANNER_LEFT_WIDE => 'Banner Left Wide',
            self::DISCOUNT_ROW => 'Discount Row',
        };
    }

    public function getEditTitle(): ?string
    {
        return "Edit {$this->getPageTitle()}";
    }

    public function getAddRoute(): string
    {
        return ContentBlockLinks::getAddRoute($this);
    }

    public function getEditRoute(string $id): string
    {
        return ContentBlockLinks::getEditRoute($this, $id);
    }

    public function getListRoute(): string
    {
        return ContentBlockLinks::getListRoute($this);
    }

    public function getDeleteRoute(string $id): string
    {
        return ContentBlockLinks::getDeleteRoute();
    }
    case HERO = 'hero_section';
    case SMALL_BANNER = 'small_banner';
    case SUMMER_BANNER = 'summer_banner';
    case BIG_BANNER = 'big_banner';
    case BANNER_SQUARE = 'banner_square';
    case BANNER_LEFT_WIDE = 'banner_left_wide';
    case DISCOUNT_ROW = 'discount_row';
}