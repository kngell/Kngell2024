<?php

declare(strict_types=1);

final class ContentBlockServiceFactory
{
    public function __construct(
        private ContentBlockModel $model,
        private ContentBlockShowModel $showModel,
        private ImageOptimizerFactory $imageOptimizerFactory,
        private ContentBlockCacheManagerFactory $factory,
        private HtmlSectionPresentationService $presenter,
    ) {
    }

    public function create(BlockType $blockType): CacheableSectionServiceInterface
    {
        return match ($blockType) {
            BlockType::HERO,BlockType::SUMMER_BANNER => new SingleContentBlockService(
                $this->model,
                $this->imageOptimizerFactory,
                $blockType,
                $this->factory->setBlockType($blockType),
                $this->presenter,
            ),
            BlockType::SMALL_BANNER,BlockType::BIG_BANNER => new CollectionContentBlockService(
                $this->showModel,
                $this->imageOptimizerFactory,
                $blockType,
                $this->factory->setBlockType($blockType),
                $this->presenter,
            ),
        };
    }
}