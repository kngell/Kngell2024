<?php

declare(strict_types=1);

final class ListDecorator extends AbstractListDecorator
{
    protected AbstractTableConfigFactory $factory;
    protected PaginatedEntityAdapterInterface $adapter;

    // ─── Derived lazily from $factory ────────────────────────
    private ?TableConfig $tableConfig = null;
    private ?AdminHeaderConfig $headerConfig = null;
    private ?string $resolvedSearchPlaceholder = null;

    public function __construct(
        private readonly SectionRenderer $sectionRenderer,
        private readonly HtmlEscaper $escaper,
        private readonly FlashRenderer $flashRenderer,
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

    // ─── Hooks consumed by parent classes ────────────────────

    protected function validateTarget(Controller $target): void
    {
        $expected = $this->tableConfig()->expectedControllerClass;
        if (!$target instanceof $expected) {
            throw new HtmlDecoratorException(sprintf(
                '%s requires %s, got %s',
                static::class,
                $expected,
                get_class($target),
            ));
        }
    }

    protected function getAdapter(): PaginatedEntityAdapterInterface
    {
        $this->ensureConfigured();
        return $this->adapter;
    }

    protected function getCacheKey(string $entityClass): string
    {
        return $this->tableConfig()->entityKey;
    }

    protected function getHeaderConfig(): ?AdminHeaderConfig
    {
        return isset($this->factory) ? $this->headerConfig() : null;
    }

    protected function getSearchPlaceholder(): string
    {
        $this->ensureConfigured();
        return $this->resolvedSearchPlaceholder ??= $this->factory->searchPlaceholder();
    }

    protected function createInstance(
        array $items,
        Controller $target,
        array $paginations = [],
    ): AdminListElementsInterface {
        $tableConfig = $this->tableConfig();

        $provider = new TableSectionProvider(
            $target->getBuilder(),
            $this->escaper,
            $tableConfig,
            $this->iconBuilder,
        );

        return new ListTable(
            $items,
            $tableConfig,
            $target->getBuilder(),
            $this->iconBuilder,
            $target->getSectionManager(),
            $this->sectionRenderer,
            $provider,
            $this->flashRenderer,
        );
    }

    // ─── Lazy accessors for derived state ────────────────────

    private function tableConfig(): TableConfig
    {
        $this->ensureConfigured();
        return $this->tableConfig ??= $this->factory->createTableConfig();
    }

    private function headerConfig(): AdminHeaderConfig
    {
        $this->ensureConfigured();
        return $this->headerConfig ??= $this->factory->createHeaderConfig();
    }

    // ─── Internal ────────────────────────────────────────────

    private function ensureConfigured(): void
    {
        if (!isset($this->factory, $this->adapter)) {
            throw new LogicException(sprintf(
                '%s used without configure(["factory" => ..., "adapter" => ...]).',
                static::class,
            ));
        }
    }
}