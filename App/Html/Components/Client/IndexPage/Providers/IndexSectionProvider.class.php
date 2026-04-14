<?php

declare(strict_types=1);

class IndexSectionProvider extends AbstractSectionProvider
{
    public function __construct(
        IconBuilder $iconBuilder,
        private HeroService $heroService,
        private SmallBannerService $smallBannerService,
        private CategoryService $category,
    ) {
        parent::__construct($iconBuilder);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        $sections = [
            'hero_section' => new HeroSection(
                $html,
                $this->iconBuilder,
                $this->heroService,
            ),
            'small_banner_section' => new SmallBannerSection(
                $html,
                $this->iconBuilder,
                $this->smallBannerService,
            ),
            'category-section' => new HomePageCategoriesSection($html, $this->iconBuilder),
        ];

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
    }
}