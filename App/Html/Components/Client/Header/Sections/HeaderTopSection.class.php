<?php

declare(strict_types=1);

class HeaderTopSection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private MenuItems $menuItems,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function getKey(): string
    {
        return 'header_top';
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $menuItems = $this->menuItems->getMenu();

        $navigationItems = [];
        $accountDropdown = null;

        foreach ($menuItems as $key => $item) {
            // Check if this is the account dropdown
            if ($key === 'Account' && is_array($item) && isset($item['items'])) {
                $accountDropdown = $item;
            }
            // Add to navigation if it's a valid menu item (not empty and not a separator)
            elseif (is_array($item) && !empty($item)) {
                $navigationItems[$key] = $item;
            }
        }

        return [
            $this->mobileToggle(),
            $this->logeMenu(),
            $this->menuSearchForm(),
            $this->navigationMenu($navigationItems),
            $this->actionsMenu($accountDropdown),
        ];
    }

    private function mobileToggle(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        return $html->button('button')
            ->class('menu__mobile-toggle', 'js-mobile-menu-toggle')
            ->add(
                $this->iconBuilder->createIcon('icon-hamburger-menu', 'Mobile menu', ['logo']),
            );
    }

    private function logeMenu(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        return $html->tag('div')->class('menu__logo')->add(
            $html->tag('a')->href('/ecommerce')->class('logo-container')->add(
                $this->iconBuilder->createIcon('icon-logo', 'Logo', ['logo']),
            ),
        );
    }

    private function menuSearchForm(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        return $html->form()->class('menu__search')->add(
            $html->button()->class('menu__search--btn')->add(
                $this->iconBuilder->createIcon('icon-search', 'Search', ['search']),
            ),
            $html->input('text')
                ->name('search')
                ->id('menu__search--input')
                ->class('menu__search--input')
                ->placeholder('Type to search...'),
        );
    }

    private function navigationMenu(array $menuItems): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $menuElements = [];

        foreach ($menuItems as $key => $menuConfig) {
            // Skip empty items (shouldn't happen after filtering, but just in case)
            if (empty($menuConfig)) {
                continue;
            }
            $menuElements[] = $this->regularMenuItem($html, $key, $menuConfig);
        }

        return $html->tag('nav')->class('menu__nav')->add(
            $html->tag('ul')->class('menu__nav-list')->add(
                ...$menuElements,
            ),
        );
    }

    private function regularMenuItem(HtmlBuilder $html, string $key, array|string $menuConfig): AbstractHtmlComponent
    {
        $li = $html->tag('li')->class('menu__nav-list__item');

        if (is_string($menuConfig)) {
            // Simple string path (backward compatibility)
            $link = $html->tag('a')
                ->href($menuConfig)
                ->class('nav-link')
                ->content($key);
        } else {
            // Array config
            $title = $menuConfig['title'] ?? $key;
            $path = $menuConfig['path'] ?? '#';

            $linkContent = [];

            // Add icon if it exists
            if (isset($menuConfig['icon'])) {
                $linkContent[] = $this->iconBuilder->createIcon(
                    $menuConfig['icon'],
                    $title,
                    ['nav-icon'],
                );
            }

            // Add title text
            $linkContent[] = $html->tag('span')->class('nav-text')->content($title);

            $link = $html->tag('a')
                ->href($path)
                ->class('nav-link')
                ->add(...$linkContent);
        }

        return $li->add($link);
    }

    private function actionsMenu(?array $accountConfig): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        // Wishlist link
        $wishlistLink = $html->tag('a')
            ->href('#')
            ->class('menu__actions-link', 'menu__actions--wishlist')
            ->add(
                $this->iconBuilder->createIcon('icon-wishlist', 'Wishlist', ['wishlist-icon']),
            );

        // Cart link
        $cartLink = $html->tag('a')
            ->href('#')
            ->class('menu__actions-link', 'menu__actions--cart')
            ->custom(['data-count' => 0])
            ->add(
                $this->iconBuilder->createIcon('icon-cart', 'Shopping Cart', ['cart-icon']),
            );

        // User section
        if (!$accountConfig || empty($accountConfig['items'])) {
            // No account items - show simple login link
            $userSection = $html->tag('a')
                ->href('/login')
                ->class('menu__actions-link', 'menu__actions--user')
                ->add(
                    $this->iconBuilder->createIcon('icon-user', 'Login', ['user-icon']),
                );
        } else {
            // Build full dropdown
            $userSection = $this->buildUserDropdown($html, $accountConfig);
        }

        return $html->tag('div')->class('menu__actions')->add(
            $wishlistLink,
            $cartLink,
            $userSection,
        );
    }

    private function buildUserDropdown(HtmlBuilder $html, array $accountConfig): AbstractHtmlComponent
    {
        $dropdownTitle = $accountConfig['title'] ?? 'Account';
        $dropdownIcon = $accountConfig['icon'] ?? null;

        // Check if there are any visible items
        $visibleItems = $this->filterVisibleItems($accountConfig['items'] ?? []);

        if (empty($visibleItems)) {
            // No visible items - show login link
            return $html->tag('a')
                ->href('/login')
                ->class('menu__actions-link', 'menu__actions--user')
                ->add(
                    $this->iconBuilder->createIcon('icon-user', 'Login', ['user-icon']),
                );
        }

        // Create unique ID for accessibility
        $dropdownId = 'user-menu-dropdown-' . uniqid();

        // Build trigger button with proper ARIA attributes
        $triggerContent = [];

        if ($dropdownIcon) {
            $triggerContent[] = $this->iconBuilder->createIcon(
                $dropdownIcon,
                $dropdownTitle,
                ['user-icon'],
            );
        } else {
            $triggerContent[] = $this->iconBuilder->createIcon(
                'icon-user',
                $dropdownTitle,
                ['user-icon'],
            );
        }

        $triggerContent[] = $html->tag('span')->class('visually-hidden')->content($dropdownTitle);

        $trigger = $html->button('button')->class('user-menu__trigger', 'menu__actions-link', 'menu__actions--user')
             ->aria('haspopup', 'true', 'expanded', 'false', 'label', $dropdownTitle, 'controls', $dropdownId)
             ->add(...$triggerContent);

        // Build dropdown menu items
        $menuList = $html->tag('ul')->class('user-menu__list');
        $hasItems = false;
        $lastWasSeparator = false;

        foreach ($visibleItems as $item) {
            // Handle separators
            if ($item === 'separator') {
                if ($hasItems && !$lastWasSeparator) {
                    $menuList->add(
                        $html->tag('li')->class('user-menu__divider')->attribute('role', 'separator'),
                    );
                    $lastWasSeparator = true;
                }
                continue;
            }

            // Handle menu items
            if (is_array($item) && isset($item['key'])) {
                $title = $item['title'] ?? $item['key'];
                $path = $item['path'] ?? '#';

                $linkContent = [];

                if (isset($item['icon'])) {
                    $linkContent[] = $this->iconBuilder->createIcon(
                        $item['icon'],
                        $title,
                        ['user-menu__icon'],
                    );
                }

                $linkContent[] = $html->tag('span')->content($title);

                $link = $html->tag('a')
                    ->href($path)
                    ->class('user-menu__link')
                    ->attribute('role', 'menuitem')
                    ->add(...$linkContent);

                // Style logout items differently
                if ($item['key'] === 'Logout' || stripos($item['key'], 'logout') !== false) {
                    $link->class('user-menu__link--logout');
                }

                $menuList->add(
                    $html->tag('li')->class('user-menu__item')->attribute('role', 'presentation')->add($link),
                );
                $hasItems = true;
                $lastWasSeparator = false;
            }
        }

        // Build the dropdown container with all ARIA attributes
        $dropdown = $html->tag('div')
            ->id($dropdownId)
            ->class('user-menu__dropdown')
            ->attribute('role', 'menu')
            ->aria('label', $dropdownTitle, 'hidden', 'true')
            ->add($menuList);

        // Wrap everything in the user-menu container
        return $html->tag('div')->class('user-menu')
            ->add($trigger)
            ->add($dropdown);
    }

    /**
     * Filter out empty items (filtered by ACL).
     */
    private function filterVisibleItems(array $items): array
    {
        return array_filter($items, function ($item) {
            // Keep separators
            if ($item === 'separator') {
                return true;
            }

            // Keep items that are non-empty arrays (have data)
            if (is_array($item) && !empty($item)) {
                return true;
            }

            // Filter out empty items (ACL filtered)
            return false;
        });
    }
}