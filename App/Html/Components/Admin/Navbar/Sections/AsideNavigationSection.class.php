<?php

declare(strict_types=1);

class AsideNavigationSection extends AbstractBaseHtmlSection
{
    private const string MENU_PATH = APP . 'menu_items_admin.json';

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private MenuItemComponentInterface $menu,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getKey(): string
    {
        return 'admin_nav_section';
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $navigationItems = $this->menu->loadNavigationItems(self::MENU_PATH);
        $menuItems = $this->menu->buildMenuItems($navigationItems);
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
}