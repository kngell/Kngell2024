<?php

declare(strict_types=1);

class HeaderSectionProvider extends AbstractSectionProvider
{
    public function __construct(
        IconBuilder $iconBuilder,
        private MenuItems $menuItems,
        private CategoryService $navService,
    ) {
        parent::__construct($iconBuilder);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        $sections = [
            'header_top' => new HeaderTopSection($html, $this->iconBuilder, $this->menuItems),
            'header_bottom' => new HeaderBottomSection(
                $html,
                $this->iconBuilder,
                $this->navService,
            ),
        ];

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
    }
}
