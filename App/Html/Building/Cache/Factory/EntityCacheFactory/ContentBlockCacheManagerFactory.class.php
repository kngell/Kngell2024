<?php

declare(strict_types=1);

final class ContentBlockCacheManagerFactory extends AbstractHtmlSectionCacheFactory
{
    private const string CACHE_FOLDER = 'content_block';

    private BlockType $blockType;

    /**
     * @param BlockType $blockType
     *
     * @return ContentBlockCacheManagerFactory
     */
    public function setBlockType(BlockType $blockType): ContentBlockCacheManagerFactory
    {
        $this->blockType = $blockType;

        return $this;
    }

    #[Override]
    protected function pageTTl(): int
    {
        return 3600;
    }

    #[Override]
    protected function entityTtl(): int
    {
        return 3600;
    }

    protected function cacheFolder(): string
    {
        return self::CACHE_FOLDER;
    }

    protected function entityClass(): string
    {
        return match ($this->blockType) {
            BlockType::HERO => ContentBlock::class,
            BlockType::SMALL_BANNER,BlockType::BIG_BANNER => ContentBlockShow::class,
            default => ContentBlock::class
        };
    }

    protected function cacheSubFolder(): string
    {
        return $this->blockType->value;
    }
}