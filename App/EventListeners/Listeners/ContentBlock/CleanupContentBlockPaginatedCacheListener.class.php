<?php

declare(strict_types=1);

class CleanupContentBlockPaginatedCacheListener extends AbstractCleanupPaginatedCacheListener
{
    public function __construct(
        PaginatedCacheFactory $cacheFactory,
        private readonly ContentBlockModelFactory $modelFactory,
        private readonly HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($cacheFactory);
    }

    protected function getPaginatedAdapter(): PaginatedEntityAdapterInterface
    {
        if ($this->blockType === null) {
            throw new InvalidArgumentException('Block type is required for paginated adapter.');
        }
        return new ContentBlockPaginatedAdapter(
            model: $this->modelFactory->createForType($this->blockType),
            blockType: $this->blockType->value,
        );
    }

    protected function getCacheFolder(): string
    {
        if ($this->blockType === null) {
            throw new InvalidArgumentException('Block type is required');
        }
        $tableConfig = new ContentBlockTableConfigFactory(
            presenter: $this->presenter,
            type: $this->blockType->value,
        );
        return $tableConfig->createTableConfig()->entityKey;
    }
}