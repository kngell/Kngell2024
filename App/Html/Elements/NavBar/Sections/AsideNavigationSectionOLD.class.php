<?php

declare(strict_types=1);

class AsideNavigationSectionOLD implements AdminNavigationSectionInterface
{
    private const string MENU_PATH = APP . 'menu_items_admin.json';

    public function __construct(private HtmlBuilder $builder, private IconBuilder $icon)
    {
    }

    public function getSection(): AbstractHtmlComponent
    {
        $html = $this->builder;

        return $html->tag('ul')->class('menu-list')->add(
            ...$this->getMenuItems(),
        );
    }

    /**
     * @return AbstractHtmlComponent[]
     */
    private function getMenuItems(): array
    {
        $menu = (new JsonFile(self::MENU_PATH))->getContentAsArray();
        $menuItems = [];
        foreach ($menu as $menuName => $menuConfig) {
            $type = $menuConfig['type'];
            unset($menuConfig['type']);
            if ($type === 'regular') {
                $menuItems[] = $this->getRegularMenulist($menuName, ['menu-list__item'], ['menu-list__item--link'], $menuConfig);
            } else {
                $menuitems[] = $this->getDropdownMenulist($menuName, $menuConfig);
            }
        }
        return $menuItems;
    }

    private function getDropdownMenulist(string $menuName, array $config): AbstractHtmlComponent
    {
        $html = $this->builder;
        $menuItems = [];
        $type = $config['type'];
        unset($config['type']);
        unset($config['link']);
        $iconLeft = $config['icon-left'];
        $iconRight = $config['icon-right'];
        unset($config['icon-left']);
        unset($config['icon-right']);
        $button = $html->button()->class('menu-list__item--dropdown-button')->add(
            $this->icon->createIcon($html, $iconLeft['name'], $iconLeft['aria'], $iconLeft['class']),
            $html->tag('span')->content($menuName),
            $this->icon->createIcon($html, $iconRight['name'], $iconRight['aria'], $iconRight['class']),
        );
        $dropdownMenu = $html->tag('ul')->class('menu-list__item--dropdown-menu')->add(
            $html->tag('li')->role('presentation')->class('wrapper')->add(
                ...$this->getDropdownItems($config),
            ),
        );
        return $html->tag('li')->class('menu-list__item')->add(
            $button,
            $dropdownMenu,
        );
    }

    /**
     * @return AbstractHtmlComponent[]
     */
    private function getDropdownItems(array $config): array
    {
        $dropDownItems = [];
        foreach ($config as $menuName => $menuConfig) {
            $dropDownItems[] = $this->getRegularMenulist($menuName, ['dropdown-list__item'], ['dropdown-list__item--link'], $menuConfig);
        }
        return $dropDownItems;
    }

    private function getRegularMenulist(string $menuName, array $class, array $linkClass, array $config): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('li')->class(...$class)->add(
            $this->getMenuLink($html, $linkClass, $config),
            $html->tag('span')->content($menuName),
        );
    }

    private function getMenuLink(HtmlBuilder $html, array $linkClass, array $config): AbstractHtmlComponent
    {
        $icon = $config['icon'];
        return  $html->tag('a')->href($config['link'])->class(...$linkClass)->add(
            $this->icon->createIcon($html, $icon['name'], $icon['aria'], $icon['class']),
        );
    }
}