<?php

declare(strict_types=1);

class AdminNavSectionProvider extends AbstractSectionProvider
{
    public function __construct(
        IconBuilder $iconBuilder,
        private Request $request,
        private NavigationConfigParser $config,
    ) {
        parent::__construct($iconBuilder);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        $sections = [
            'admin_nav_section' => new AsideNavigationSection(
                $html,
                $this->iconBuilder,
                new AdminMenuItemComponent(
                    $html,
                    $this->iconBuilder,
                    $this->config,
                    $this->request->getPathFromUri(),
                ),
            ),
        ];

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
    }
}