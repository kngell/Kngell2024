<?php

declare(strict_types=1);

class CategoryFormSectionProvider extends AbstractSectionProvider
{
    public function __construct(
        IconBuilder $iconBuilder,
        private readonly FormSectionHeader $header,
        private CategoryService $service,
    ) {
        parent::__construct($iconBuilder);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        $sections = [
            'basic-information' => new CategoryBasicInformation($html, $this->iconBuilder, $this->header, $this->service),
            'social-media' => new CategorySocialMediaSection($html, $this->iconBuilder, $this->header),
            'open-graph' => new CategoryOpenGraphSection($html, $this->iconBuilder, $this->header),
            'category-media' => new CategoryMediaSection($html, $this->iconBuilder, $this->header),
            'og-image' => new CategoryOGMediaSection($html, $this->iconBuilder, $this->header),
            'canonical-infos' => new CategoryCanonicalInfoSection($html, $this->iconBuilder, $this->header),
            'content-area' => new CategoryContentSection($html, $this->iconBuilder, $this->header),
            'content-style' => new CategoryContentStyleSection($html, $this->iconBuilder, $this->header),
            'navigation-infos' => new CategoryNavigationSection($html, $this->iconBuilder, $this->header),
            'price-range' => new CategoryPriceRangeSection($html, $this->iconBuilder, $this->header, new PriceRangesFieldMapping()),
        ];

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
    }
}