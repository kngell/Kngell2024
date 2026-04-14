<?php

declare(strict_types=1);

class AsideNavigationSection extends AbstractBaseHtmlSection
{
    private const string MENU_PATH = APP . 'menu_items_admin.json';

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private NavigationConfigParser $configParser,
        private MenuItemComponentInterface $menuItem,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getKey(): string
    {
        return 'admin_nav_section';
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $navigationItems = $this->loadNavigationItems();
        $menuItems = $this->buildMenuItems($navigationItems);
        $html = $this->htmlBuilder;
        return $html->tag('nav')
            ->aria('label', 'Admin Navigation')
            ->add(
                $html->tag('ul')
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
                    $menuItems[] = $this->menuItem->getRegularItem(
                        $item,
                        ['menu-list__item'],
                        ['menu-list__item--link'],
                    );
                } elseif ($item->type === 'dropdown') {
                    $menuItems[] = $this->menuItem->getDropdownItem($item);
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