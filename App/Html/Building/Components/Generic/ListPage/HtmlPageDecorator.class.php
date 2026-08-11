<?php

declare(strict_types=1);

class HtmlPageDecorator extends AbstractListDecorator
{
    protected ?PageConfig $config = null;
    protected ?AbstractPageConfigFactory $factory = null;

    /**
     * @var PaginatedEntityAdapterInterface|array<PaginatedEntityAdapterInterface>
     */
    protected PaginatedEntityAdapterInterface|array $adapter;

    public function __construct(
        private readonly PageFactory $pageFactory,
        PaginatedCacheFactory $cacheFactory,
        PaginationStateService $paginationService,
        IconBuilder $iconBuilder,
        AdminMainHeaderFactory $adminHeaderFactory,
    ) {
        parent::__construct(
            cacheFactory: $cacheFactory,
            paginationService: $paginationService,
            iconBuilder: $iconBuilder,
            adminHeaderFactory: $adminHeaderFactory,
        );
    }

    #[Override]
    protected function validateTarget(Controller $target): void
    {
        $expected = $this->pageConfig()->getExpectedControllerClass();
        if (!$target instanceof $expected) {
            throw new HtmlDecoratorException(sprintf(
                '%s requires %s, got %s',
                static::class,
                $expected,
                get_class($target),
            ));
        }
    }

    #[Override]
    protected function getAdapter(): PaginatedEntityAdapterInterface|array
    {
        $this->ensureConfigured();
        return $this->adapter;
    }

    #[Override]
    protected function getCacheKey(string $entityClass): string
    {
        $entityKey = EntityKey::fromEntityClass($entityClass);
        if ($entityKey === null) {
            throw new InvalidArgumentException(sprintf('Unknown entity class: %s', $entityClass));
        }
        return $entityKey->getKey();
    }

    #[Override]
    protected function createInstance(array $items, Controller $target, array $pagination = []): AdminListElementsInterface
    {
        return $this->pageFactory->getPage($this->config, $items, $pagination);
    }

    private function pageConfig(): PageConfig
    {
        $this->ensureConfigured();
        if ($this->config === null) {
            $this->config = $this->factory->createPageConfig();
        }
        return $this->config;
    }

    private function ensureConfigured(): void
    {
        if (!isset($this->factory, $this->adapter)) {
            throw new LogicException(sprintf(
                '%s must be configured with configure(["factory" => ..., "adapter" => ...])',
                static::class,
            ));
        }
    }
}