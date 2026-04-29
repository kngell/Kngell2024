<?php

declare(strict_types=1);

class ProductListDecorator extends AbstractListDecorator
{
    protected const array BREADCRUMBS_LINKS = ['Dashboard', 'Product List'];
    protected const array HEADER_BTN_CONFIG = [
        [
            'type' => 'submit',
            'label' => 'Export',
            'action' => '/products/export',
            'formName' => 'product_export_form',
            'requiresEditMode' => false,
            'requiresEntityId' => true,
            'size' => 'md-compact',
            'ariaLabel' => 'Export',
            'style' => 'secondary',
            'icon' => 'icon-export',
            'iconPosition' => 'left',
            'attributes' => [],
            'class' => ['export'],
        ],
        [
            'type' => 'submit',
            'label' => 'Add New',
            'action' => '/admin/product-add',
            'formName' => 'product_add_form',
            'requiresEditMode' => false,
            'requiresEntityId' => false,
            'size' => 'md-compact',
            'ariaLabel' => 'Add New',
            'style' => 'primary',
            'icon' => 'icon-plus',
            'iconPosition' => 'left',
            'attributes' => [],
            'class' => [],
        ],
    ];

    public function __construct(
        private readonly ProductShowModel $model,
        private readonly SectionRenderer $sectionRenderer,
        private readonly ProductTableSectionProvider $provider,
        PaginatedCacheFactory $cacheFactory,
        PaginationStateService $paginationService,
        IconBuilder $iconBuilder,
        AdminMainHeaderFactory $adminHeaderFactory,
    ) {
        parent::__construct(
            $cacheFactory,
            $paginationService,
            $iconBuilder,
            $adminHeaderFactory,
        );
    }

    protected function validateTarget(Controller $target): void
    {
        if (!$target instanceof AdminController) {
            throw new HtmlDecoratorException(sprintf('%s requires AdminController, got %s', self::class, get_class($target)));
        }
    }

    // Now correctly using PaginatedEntityAdapterInterface
    protected function getAdapter(): PaginatedEntityAdapterInterface
    {
        return new ProductPaginatedAdapter($this->model);
    }

    protected function getCacheKey(): string
    {
        return EntityCacheFolder::PRODUCT->value;
    }

    protected function getTablePrefix(): string
    {
        return 'product';
    }

    protected function headerTitle(): ?string
    {
        return 'Product List';
    }

    protected function createTableInstance(array $items, Controller $target): ListTableInterface
    {
        return new ProductListTable(
            $items,
            $target->builder,
            $this->iconBuilder,
            $target->sectionManager,
            $this->sectionRenderer,
            $target->flash,
            $this->provider,
        );
    }
}