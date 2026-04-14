<?php

declare(strict_types=1);

class AdminMenuItemComponent implements MenuItemComponentInterface
{
    public function __construct(
        private HtmlBuilder $builder,
        private IconBuilder $iconBuilder,
        private string $currentPath,
    ) {
        $this->currentPath = $this->normalizePath($currentPath);
    }

    public function getRegularItem(NavigationItem $item, array $liClasses, array $linkClasses): AbstractHtmlComponent
    {
        $isActive = $this->isActivePath($item->link);
        $activeClasses = $isActive ? ['active'] : [];

        $link = $this->builder->tag('a')
            ->href($item->link)
            ->class(...array_merge($linkClasses, $activeClasses))
            ->role('menuitem')
            ->aria('current', $isActive ? 'page' : 'false')
            ->add(
                $this->iconBuilder->createIcon(
                    $this->builder,
                    $item->icon->name,
                    $item->icon->aria,
                    $item->icon->classes,
                ),
                $this->builder->tag('span')->content($item->name),
            );

        return $this->builder->tag('li')
            ->class(...array_merge($liClasses, $activeClasses))
            ->role('none')
            ->add($link);
    }

    public function getDropdownItem(NavigationItem $item): AbstractHtmlComponent
    {
        $hasActiveChild = false;
        $activeDropdownItems = [];
        $button = $this->builder->button()
            ->class('menu-list__item--dropdown-button')
            ->ariaLabel("{$item->name} menu")
            ->aria('haspopup', 'true')
            ->aria('expanded', 'false')
            ->aria('controls', $this->generateDropdownId($item))
            ->add(
                $this->iconBuilder->createIcon(
                    $this->builder,
                    $item->iconLeft->name,
                    $item->iconLeft->aria,
                    $item->iconLeft->classes,
                ),
                $this->builder->tag('span')->content($item->name),
                $this->iconBuilder->createIcon(
                    $this->builder,
                    $item->iconRight->name,
                    $item->iconRight->aria,
                    $item->iconRight->classes,
                ),
            );
        $dropdownId = $this->generateDropdownId($item);

        $dropdownItems = [];
        foreach ($item->dropdownItems as $dropdownName => $dropdownLink) {
            $isActive = $this->isActivePath($dropdownLink);

            if ($isActive) {
                $hasActiveChild = true;
                $activeDropdownItems[] = $dropdownName;
            }

            $dropdownItems[] = $this->builder->tag('li')
                ->class('dropdown-list__item', $isActive ? 'active' : '')
                ->role('none')
                ->add(
                    $this->builder->tag('a')
                        ->href($dropdownLink)
                        ->class('dropdown-list__item--link')
                        ->role('menuitem')
                        ->aria('current', $isActive ? 'page' : 'false')
                        ->content($dropdownName),
                );
        }

        $dropdownMenu = $this->builder->tag('ul')
            ->id($dropdownId)
            ->class('menu-list__item--dropdown-menu')
            ->role('menu')
            ->aria('labelledby', $this->generateButtonId($item))
            ->add(
                $this->builder->tag('div')
                    ->role('presentation')
                    ->class('dropdown-wrapper')
                    ->add(...$dropdownItems),
            );

        $liClasses = ['menu-list__item'];
        $ariaExpanded = 'false';

        if ($hasActiveChild) {
            $liClasses[] = 'active';
            $liClasses[] = 'opened';
            $liClasses[] = 'has-active-child';
            $ariaExpanded = 'true';
        }

        $button->id($this->generateButtonId($item))
               ->aria('expanded', $ariaExpanded);

        return $this->builder->tag('li')
            ->class(...$liClasses)
            ->role('none')
            ->add($button, $dropdownMenu);
    }

    private function generateDropdownId(NavigationItem $item): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $item->name));
        return "dropdown-menu-{$slug}";
    }

    private function generateButtonId(NavigationItem $item): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $item->name));
        return "dropdown-button-{$slug}";
    }

    private function isActivePath(string $itemPath): bool
    {
        $normalizedItemPath = $this->normalizePath($itemPath);

        if ($normalizedItemPath === $this->currentPath) {
            return true;
        }
        if ($this->currentPath === '' || $this->currentPath === '/') {
            return $normalizedItemPath === '/' || $normalizedItemPath === '';
        }
        if ($normalizedItemPath !== '' &&
            $normalizedItemPath !== '/' &&
            str_starts_with($this->currentPath, $normalizedItemPath . '/')) {
            $remaining = substr($this->currentPath, strlen($normalizedItemPath) + 1);

            return $this->isValidRemainingSegment($remaining);
        }

        return false;
    }

    private function isValidRemainingSegment(string $segment): bool
    {
        return preg_match('/^\d+$/', $segment) ||
               preg_match('/^[a-f0-9\-]{36}$/i', $segment) ||
               preg_match('/^[a-z0-9\-]+$/i', $segment) ||
               preg_match('/^[a-z0-9_]+$/i', $segment);
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);

        // Ensure path starts with /
        if ($path !== '' && !str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        // Remove trailing slash except for root
        if ($path !== '/' && $path !== '') {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}