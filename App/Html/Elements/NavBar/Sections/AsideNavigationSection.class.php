<?php

declare(strict_types=1);

class AsideNavigationSection implements AdminNavigationSectionInterface
{
    private const string MENU_PATH = APP . 'menu_items_admin.json';

    public function __construct(
        private HtmlBuilder $builder,
        private NavigationConfigParser $configParser,
        private MenuItemFactoryInterface $menuItemFactory,
    ) {
    }

    public function getSection(): AbstractHtmlComponent
    {
        $navigationItems = $this->loadNavigationItems();
        $menuItems = $this->buildMenuItems($navigationItems);

        return $this->builder->tag('nav')
            ->aria('label', 'Admin Navigation')
            ->add(
                $this->builder->tag('ul')
                    ->class('menu-list')
                    ->role('menubar')
                    ->add(...$menuItems),
            );
    }

    private function loadNavigationItems(): array
    {
        try {
            $config = (new JsonFile(self::MENU_PATH))->getContentAsArray();
            return $this->configParser->parse($config);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Failed to load navigation config: ' . $e->getMessage(), $e->getCode());
        }
    }

    private function buildMenuItems(array $navigationItems): array
    {
        $menuItems = [];

        foreach ($navigationItems as $item) {
            try {
                if ($item->type === 'regular') {
                    $menuItems[] = $this->menuItemFactory->createRegularItem(
                        $item,
                        ['menu-list__item'],
                        ['menu-list__item--link'],
                    );
                } elseif ($item->type === 'dropdown') {
                    $menuItems[] = $this->menuItemFactory->createDropdownItem($item);
                }
            } catch (Exception $e) {
                // Skip invalid items but log the error
                error_log("Failed to create menu item '{$item->name}': " . $e->getMessage());
                continue;
            }
        }

        return $menuItems;
    }
}