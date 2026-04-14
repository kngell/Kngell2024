<?php

declare(strict_types=1);

class SmallBannerResponse extends AbstractBaseEntityResponse
{
    private const string DEFAULT_BUTTON_TEXT = 'Shop now';
    private const string DEFAULT_BUTTON_LINK = '#';
    private const Theme DEFAULT_THEME = Theme::LIGHT;

    public function __construct(
        array $image,
        ?SmallBannerShow $banner,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $banner, $isDefault);
    }

    public function getBanner(): ?SmallBannerShow
    {
        return $this->entity;
    }

    public function getCustomTitle(bool $custom = true): ?string
    {
        $banner = $this->getBanner();
        if (!$banner) {
            return null;
        }

        return $custom
            ? $banner->getCustomTitle()
            : $banner->getProduct()->getName();
    }

    public function getTitleSpan(bool $custom = true): ?string
    {
        $banner = $this->getBanner();
        if (!$banner) {
            return null;
        }

        return $custom
            ? $banner->getCustomTitleSpan()
            : null;
    }

    /**
     * Get subtitle text.
     *
     * @param bool $custom Whether to use custom subtitle or product description
     */
    public function getSubtitle(bool $custom = true): ?string
    {
        $banner = $this->getBanner();
        if (!$banner) {
            return null;
        }

        return $custom
            ? $banner->getCustomSubtitle()
            : $banner->getProduct()->getShortDescription();
    }

    /**
     * @param bool $custom Whether to use custom description or product description
     */
    public function getDescription(bool $custom = true): string
    {
        $banner = $this->getBanner();
        if (!$banner) {
            return '';
        }

        return $custom
            ? $banner->getCustomDescription() ?? ''
            : $banner->getProduct()->getDescription() ?? '';
    }

    public function getClass(): ?string
    {
        return $this->getBanner()?->getSmallBannerClass()->value;
    }

    public function getEnumClass(): SmallBannerClass
    {
        return $this->getBanner()?->getSmallBannerClass();
    }

    public function getButtonText(): string
    {
        return $this->getBanner()?->getCustomButtonText() ?? self::DEFAULT_BUTTON_TEXT;
    }

    public function getButtonLink(): string
    {
        return $this->getBanner()?->getCustomButtonLink() ?? self::DEFAULT_BUTTON_LINK;
    }

    public function getTheme(): Theme
    {
        return $this->getBanner()?->getSmallBannerTheme() ?? self::DEFAULT_THEME;
    }

    public function getBannerLeftSquare1()
    {
    }

    /**
     * Check if banner has custom content.
     */
    public function hasCustomContent(): bool
    {
        $banner = $this->getBanner();
        if (!$banner) {
            return false;
        }

        return $banner->getCustomTitle() !== null
            || $banner->getCustomSubtitle() !== null
            || $banner->getCustomDescription() !== null
            || $banner->getCustomButtonText() !== null
            || $banner->getCustomImageUrl() !== null;
    }
}