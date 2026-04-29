<?php

declare(strict_types=1);

class HeroListDecorator extends AbstractListDecorator
{
    protected const array BREADCRUMBS_LINKS = ['Dashboard', 'Hero List'];
    protected const array HEADER_BTN_CONFIG = [
        [
            'type' => 'submit',
            'label' => 'Export',
            'action' => '/hero-page/export',
            'formName' => 'hero_export_form',
            'requiresEditMode' => false,
            'requiresEntityId' => true,
            'size' => 'md-compact',
            'ariaLabel' => 'Export hero data',
            'style' => 'secondary',
            'icon' => 'icon-export',
            'iconPosition' => 'left',
            'attributes' => [],
            'class' => ['export'],
        ],
        [
            'type' => 'submit',
            'label' => 'Add New',
            'action' => '/hero-page/add',
            'formName' => 'hero_add_form',
            'requiresEditMode' => false,
            'requiresEntityId' => false,
            'size' => 'md-compact',
            'ariaLabel' => 'Add new hero',
            'style' => 'primary',
            'icon' => 'icon-plus',
            'iconPosition' => 'left',
            'attributes' => [],
            'class' => [],
        ],
    ];

    public function __construct(
        private readonly HeroModel $model,
        private readonly SectionRenderer $sectionRenderer,
        private readonly HeroTableSectionProvider $provider,
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
        if (!$target instanceof HeroListController) {
            throw new HtmlDecoratorException(
                sprintf(
                    '%s requires %s, got %s',
                    static::class,
                    HeroListController::class,
                    get_class($target),
                ),
            );
        }
    }

    protected function getAdapter(): PaginatedEntityAdapterInterface
    {
        return new HeroPaginatedAdapter($this->model);
    }

    protected function getCacheKey(): string
    {
        return 'hero';
    }

    protected function headerTitle(): ?string
    {
        return 'Hero List';
    }

    protected function createTableInstance(array $items, Controller $target): ListTableInterface
    {
        assert($target instanceof HeroListController);

        return new HeroListTable(
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