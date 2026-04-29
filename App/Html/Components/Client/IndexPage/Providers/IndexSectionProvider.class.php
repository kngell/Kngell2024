<?php

declare(strict_types=1);

class IndexSectionProvider extends AbstractSectionProvider
{
    public function __construct(
        IconBuilder $iconBuilder,
        private HeroService $heroService,
        private SmallBannerService $smallBannerService,
        private CategoryFrontendService $category,
        private ProductService $product,
        private HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($iconBuilder);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        $sections = [
            new HeroSection(
                $html,
                $this->iconBuilder,
                $this->heroService,
            ),
            new SmallBannerSection(
                $html,
                $this->iconBuilder,
                $this->smallBannerService,
            ),
            new HomePageCategoriesSection(
                $html,
                $this->iconBuilder,
                $this->category,
            ),
            new HomeProductSection(
                $html,
                $this->iconBuilder,
                $this->product,
                $this->presenter,
            ),
        ];

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
    }
}