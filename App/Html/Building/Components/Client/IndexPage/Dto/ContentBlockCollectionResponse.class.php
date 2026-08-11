<?php

declare(strict_types=1);

class ContentBlockCollectionResponse extends AbstractBaseEntityResponse
{
    use EntityDisplayTrait;
    private const string DEFAULT_BUTTON_TEXT = 'Shop now';
    private const string DEFAULT_BUTTON_LINK = '#';
    private const SmallBannerTheme DEFAULT_THEME = SmallBannerTheme::LIGHT;

    public function __construct(
        array $image,
        ?ContentBlockShow $entity,
        bool $isDefault,
        private HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($image, $entity, $isDefault);
    }

    public function getEntity(): ?ContentBlockShow
    {
        return $this->entity;
    }

    public function getTitle(bool $custom = true): ?string
    {
        $block = $this->getEntity();
        if (!$block) {
            return null;
        }

        return $custom
            ? $this->presenter->showField($block, 'title')
            : $this->presenter->showField($block, 'product.name');
    }

    public function getTitleSpan(bool $custom = true): ?string
    {
        $block = $this->getEntity();
        if (!$block) {
            return null;
        }

        return $custom
            ? $block->get('title_span')
            : null;
    }

    /**
     * Get subtitle text.
     *
     * @param bool $custom Whether to use custom subtitle or product description
     */
    public function getSubtitle(bool $custom = true): ?string
    {
        $block = $this->getEntity();
        if (!$block) {
            return null;
        }

        return $custom
            ? $block->getSubtitle()
            : $block->getProduct()->getShortDescription();
    }

    /**
     * @param bool $custom Whether to use custom description or product description
     */
    public function getDescription(bool $custom = true): string
    {
        $block = $this->getEntity();
        if (!$block) {
            return '';
        }

        return $custom
            ? $block->get('description') ?? ''
            : $block->getProduct()->getDescription() ?? '';
    }

    public function getClass(): ?string
    {
        return $this->getEntity()?->get('position');
    }

    public function getEnumClass(): SmallBannerPosition
    {
        return SmallBannerPosition::tryFrom($this->getEntity()?->get('position'));
    }

    public function getButtonText(): string
    {
        return $this->getEntity()?->getButtonText() ?? self::DEFAULT_BUTTON_TEXT;
    }

    public function getButtonLink(): string
    {
        return $this->getEntity()?->getButtonLink() ?? self::DEFAULT_BUTTON_LINK;
    }

    public function getTheme(): SmallBannerTheme
    {
        $theme = $this->getEntity()?->get('theme') ?? self::DEFAULT_THEME;
        return SmallBannerTheme::tryFrom($theme);
    }

    public function getBannerLeftSquare1()
    {
    }

    public function hasCustomContent(): bool
    {
        $block = $this->getEntity();
        if (!$block) {
            return false;
        }

        return $block->getTitle() !== null
            || $block->getSubtitle() !== null
            || $block->get('description') !== null
            || $block->getButtonText() !== null
            || $block->get('image')['url'] !== null;
    }
}