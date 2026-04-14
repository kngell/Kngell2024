<?php

declare(strict_types=1);

class HeaderSectionProviderFactory implements SectionProviderFactoryInterface
{
    public function __construct(
        private IconBuilder $iconBuilder,
        private MenuItems $menuItems,
        private CategoryService $categoryService,
    ) {
    }

    public function create(): SectionProviderInterface
    {
        return new HeaderSectionProvider(
            $this->iconBuilder,
            $this->menuItems,
            $this->categoryService,
        );
    }
}
