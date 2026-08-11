<?php

declare(strict_types=1);

class CleanupContentBlockImageCacheListener extends AbstractSectionCacheCleanupListener
{
    public function __construct(
        ImageCacheFactory $imageCacheFactory,
        private ContentBlockCacheManagerFactory $contentBlockCacheFactory,
    ) {
        parent::__construct($imageCacheFactory);
    }

    protected function getEntityId(array $payload): ?int
    {
        return $payload['data']['id'] ?? null;
    }

    protected function getOldImageUrl(object $entity): null|string|array
    {
        if (!$entity instanceof ContentBlock) {
            return null;
        }

        $blockMetadata = $entity->getBlockMetadata();
        $blockType = $entity->getBlockType();

        return match ($blockType) {
            BlockType::HERO, BlockType::SMALL_BANNER, BlockType::BIG_BANNER => $blockMetadata['image']['url'] ?? null,
            BlockType::BANNER_SQUARE, BlockType::BANNER_LEFT_WIDE, BlockType::DISCOUNT_ROW => $blockMetadata['images'] ?? null,
            default => null,
        };
    }

    protected function getNewImageUrl(array $payload): null|string|array
    {
        $blockType = $payload['context']['block_type'] ?? null;

        return match(BlockType::tryFrom($blockType)) {
            BlockType::HERO, BlockType::SMALL_BANNER, BlockType::BIG_BANNER => $payload['media']['image_url'] ?? null,
            BlockType::BANNER_SQUARE, BlockType::BANNER_LEFT_WIDE, BlockType::DISCOUNT_ROW => $payload['media']['image_urls'] ?? $payload['media']['images'] ?? null,
            default => null,
        };
    }

    protected function getPageTarget(array $payload): string
    {
        return $payload['form_data']['page_target'] ?? 'index';
    }

    protected function getCacheManager(): HtmlSectionCacheManager
    {
        if ($this->blockType === null) {
            throw new InvalidArgumentException('BlockType is required');
        }
        return $this->contentBlockCacheFactory->setBlockType(
            $this->blockType,
        )->create();
    }

    protected function getServiceClass(): string
    {
        return match ($this->blockType) {
            BlockType::SMALL_BANNER,BlockType::BIG_BANNER => CollectionContentBlockService::class,
            default => SingleContentBlockService::class,
        };
    }

    protected function getEntityType(): string
    {
        return 'ContentBlock';
    }
}