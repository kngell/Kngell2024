<?php

declare(strict_types=1);
class IndexSectionProviderFactory implements SectionProviderFactoryInterface
{
    public function __construct(
        private IconBuilder $iconBuilder,
        private HeroService $heroService,
        private SmallBannerService $smallBannerService,
        private CategoryService $categoryService,
    ) {
    }

    public function create(): SectionProviderInterface
    {
        return new IndexSectionProvider(
            $this->iconBuilder,
            $this->heroService,
            $this->smallBannerService,
            $this->categoryService,
        );
    }
}