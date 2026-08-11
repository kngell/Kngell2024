<?php

declare(strict_types=1);

enum EntityKey: string
{
    // ─── Key Methods ──────────────────────────────────────────

    public function getKey(?BlockType $blockType = null): string
    {
        return match($this) {
            self::CONTENT_BLOCK => $this->getContentBlockKey($blockType),
            default => $this->value,
        };
    }

    public function getPlural(?BlockType $blockType = null): string
    {
        return match($this) {
            self::CONTENT_BLOCK => $this->getContentBlockPlural($blockType),
            self::HERO => 'heroes',
            self::PRODUCT => 'products',
            self::CATEGORY => 'categories',
            self::FOOTER_COLUMN => 'columns',
            self::FOOTER_COLUMN_SHOW => 'column_shows',
            self::FOOTER_ABOUT => 'abouts',
            self::FOOTER_SOCIAL => 'socials',
            self::FOOTER_MENU_LINK => 'menu_links',
            self::CHECKOUT => 'checkouts',
        };
    }

    public function getBasePath(): string
    {
        return match($this) {
            self::CONTENT_BLOCK => '/admin/content-block-page',
            self::HERO => '/admin/hero-section-page',
            self::PRODUCT => '/admin/admin',
            self::CATEGORY => '/admin/category-page',
            self::FOOTER_ABOUT => '/admin/footer-about-save',
            self::FOOTER_COLUMN, self::FOOTER_COLUMN_SHOW,self::FOOTER_SOCIAL,self::FOOTER_MENU_LINK => '/admin/footer-page',
            self::CHECKOUT => '/checkout'
        };
    }

    public function getDisplayName(?BlockType $blockType = null): string
    {
        return match($this) {
            self::CONTENT_BLOCK => $this->getContentBlockDisplayName($blockType),
            self::HERO => 'Hero Section',
            self::PRODUCT => 'Product',
            self::CATEGORY => 'Category',
            self::FOOTER_COLUMN => 'Footer Column',
            self::FOOTER_COLUMN_SHOW => 'Footer Column Show',
            self::FOOTER_ABOUT => 'Footer About',
            self::FOOTER_SOCIAL => 'Footer Social',
            self::FOOTER_MENU_LINK => 'Footer Menu Link',
        };
    }

    public function getEntityClass(): string
    {
        return match($this) {
            self::CONTENT_BLOCK => ContentBlock::class,
            self::HERO => HeroSection::class,
            self::PRODUCT => Product::class,
            self::CATEGORY => Category::class,
            self::FOOTER_COLUMN => FooterMenuColumn::class,
            self::FOOTER_COLUMN_SHOW => FooterMenuShow::class,
            self::FOOTER_ABOUT => FooterAbout::class,
            self::FOOTER_SOCIAL => FooterSocial::class,
            self::FOOTER_MENU_LINK => FooterMenuLink::class,
        };
    }

    // ─── Private Helpers ──────────────────────────────────────

    private function getContentBlockKey(?BlockType $blockType): string
    {
        if ($blockType === null) {
            throw new InvalidArgumentException('BlockType is required for CONTENT_BLOCK');
        }

        return $blockType->getEntityKey();
    }

    private function getContentBlockPlural(?BlockType $blockType): string
    {
        if ($blockType === null) {
            throw new InvalidArgumentException('BlockType is required for CONTENT_BLOCK');
        }

        return $blockType->getEntityPluralName();
    }

    private function getContentBlockDisplayName(?BlockType $blockType): string
    {
        if ($blockType === null) {
            throw new InvalidArgumentException('BlockType is required for CONTENT_BLOCK');
        }

        return $blockType->getPageTitle();
    }

    // ─── Static Helpers ────────────────────────────────────────

    public static function fromEntityClass(string $entityClass, ?BlockType $blockType = null): ?self
    {
        return match ($entityClass) {
            ContentBlock::class => self::CONTENT_BLOCK,
            ContentBlockShow::class => self::CONTENT_BLOCK_SHOW,
            HeroSection::class => self::HERO,
            Product::class => self::PRODUCT,
            Category::class => self::CATEGORY,
            FooterMenuColumn::class => self::FOOTER_COLUMN,
            FooterMenuShow::class => self::FOOTER_COLUMN_SHOW,
            FooterAbout::class => self::FOOTER_ABOUT,
            FooterSocial::class => self::FOOTER_SOCIAL,
            FooterMenuLink::class => self::FOOTER_MENU_LINK,
            UserAddress::class, => self::CHECKOUT,
            default => null,
        };
    }

    public static function fromKey(string $key): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $key) {
                return $case;
            }
        }
        return null;
    }
    // ─── Cases ────────────────────────────────────────────────

    case HERO = 'hero';
    case PRODUCT = 'product';
    case CATEGORY = 'category';
    case CONTENT_BLOCK = 'content_block';
    case CONTENT_BLOCK_SHOW = 'content_block_show';
    case FOOTER_COLUMN = 'footer_column';
    case FOOTER_COLUMN_SHOW = 'footer_column_show';  // Fixed typo
    case FOOTER_ABOUT = 'footer_about';
    case FOOTER_SOCIAL = 'footer_social';
    case FOOTER_MENU_LINK = 'footer_menu_link';
    case CHECKOUT = 'checkout';
}