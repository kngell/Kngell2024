<?php

declare(strict_types=1);

class FooterResponse extends AbstractBaseEntityResponse
{
    use EntityDisplayTrait;

    public function __construct(
        array $image,
        private HtmlSectionPresentationService $presenter,
        ?FooterMenuShow $entity,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $entity, $isDefault);
    }

    public function __toString(): string
    {
        return $this->getTitle() ?? '';
    }

    public function getEntity(): ?FooterMenuShow
    {
        return $this->entity;
    }

    public function getId(): ?int
    {
        return $this->getEntity()?->getId();
    }

    public function getTitle(): ?string
    {
        return $this->show($this->getEntity(), 'title');
    }

    public function getColumnKey(): ?string
    {
        return $this->show($this->getEntity(), 'column_key');
    }

    public function getSortOrder(): ?int
    {
        return $this->getEntity()?->getSortOrder();
    }

    public function isActive(): bool
    {
        return $this->getEntity()?->getIsActive() ?? false;
    }

    public function getMenuItems(): array
    {
        $entity = $this->getEntity();
        if ($entity === null) {
            return [];
        }

        $menuItems = $entity->getFooterMenuLink();
        if (empty($menuItems)) {
            return [];
        }

        // Filter active items and sort by sort_order
        $activeItems = array_filter($menuItems, fn ($item) => $item->getisActive());
        usort($activeItems, fn ($a, $b) => $a->getSortOrder() <=> $b->getSortOrder());

        $items = [];
        foreach ($activeItems as $menuItem) {
            $items[] = [
                'id' => $menuItem->getId(),
                'title' => $this->presenter->showField($menuItem, 'title'),
                'url' => $this->presenter->showField($menuItem, 'url'),
                'target' => $this->presenter->showField($menuItem, 'target') ?? '_self',
                'sort_order' => $menuItem->getSortOrder(),
                'is_active' => $menuItem->getIsActive(),
            ];
        }

        return $items;
    }

    public function toMenuArray(): array
    {
        $entity = $this->getEntity();
        if (!$entity) {
            return [];
        }

        return [
            'id' => $entity->getId(),
            'title' => $this->getTitle(),
            'column_key' => $entity->getColumnKey(),
            'sort_order' => $entity->getSortOrder(),
            'is_active' => $entity->getIsActive(),
            'items' => $this->getMenuItems(),
            'image' => $this->getImage(),
        ];
    }

    public function toArray(): array
    {
        return $this->toMenuArray();
    }

    public static function buildMenuStructure(array $responses, array $defaultImageData): array
    {
        $menu = ['columns' => []];

        foreach ($responses as $response) {
            if ($response->isDefault()) {
                continue;
            }

            $columnArray = $response->toMenuArray();
            if (empty($columnArray)) {
                continue;
            }

            $menu['columns'][$columnArray['column_key']] = $columnArray;
        }

        // Sort columns by sort_order
        if (!empty($menu['columns'])) {
            usort($menu['columns'], fn ($a, $b) => $a['sort_order'] <=> $b['sort_order']);

            // Re-index by column_key after sorting
            $sortedColumns = [];
            foreach ($menu['columns'] as $column) {
                $sortedColumns[$column['column_key']] = $column;
            }
            $menu['columns'] = $sortedColumns;
        }

        return $menu;
    }

    public static function getDefaultMenu(array $defaultImageData): array
    {
        return [
            'columns' => [
                'services' => [
                    'id' => null,
                    'title' => 'Services',
                    'column_key' => 'services',
                    'sort_order' => 1,
                    'is_active' => true,
                    'items' => [
                        ['title' => 'Bonus program', 'url' => '/bonus', 'target' => '_self', 'sort_order' => 0, 'is_active' => true],
                        ['title' => 'Gift cards', 'url' => '/gift-cards', 'target' => '_self', 'sort_order' => 1, 'is_active' => true],
                        ['title' => 'Credit and payment', 'url' => '/payment', 'target' => '_self', 'sort_order' => 2, 'is_active' => true],
                        ['title' => 'Service contracts', 'url' => '/contracts', 'target' => '_self', 'sort_order' => 3, 'is_active' => true],
                        ['title' => 'Non-cash account', 'url' => '/non-cash', 'target' => '_self', 'sort_order' => 4, 'is_active' => true],
                        ['title' => 'Payment', 'url' => '/payment', 'target' => '_self', 'sort_order' => 5, 'is_active' => true],
                    ],
                    'image' => $defaultImageData,
                ],
                'assistance' => [
                    'id' => null,
                    'title' => 'Assistance to the buyer',
                    'column_key' => 'assistance',
                    'sort_order' => 2,
                    'is_active' => true,
                    'items' => [
                        ['title' => 'Find an order', 'url' => '/order-status', 'target' => '_self', 'sort_order' => 0, 'is_active' => true],
                        ['title' => 'Terms of delivery', 'url' => '/delivery', 'target' => '_self', 'sort_order' => 1, 'is_active' => true],
                        ['title' => 'Exchange and return of goods', 'url' => '/returns', 'target' => '_self', 'sort_order' => 2, 'is_active' => true],
                        ['title' => 'Guarantee', 'url' => '/guarantee', 'target' => '_self', 'sort_order' => 3, 'is_active' => true],
                        ['title' => 'Frequently asked questions', 'url' => '/faq', 'target' => '_self', 'sort_order' => 4, 'is_active' => true],
                        ['title' => 'Terms of use of the site', 'url' => '/terms', 'target' => '_self', 'sort_order' => 5, 'is_active' => true],
                    ],
                    'image' => $defaultImageData,
                ],
            ],
        ];
    }
}