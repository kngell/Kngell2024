<?php

declare(strict_types=1);

final class ContentBlockFormLayoutFactory
{
    public function create(BlockType $blockType): ContentBlockFormLayoutInterface
    {
        $page = $blockType->getPageTitle();
        return match ($blockType) {
            BlockType::SMALL_BANNER,BlockType::BIG_BANNER => new RegularBlockFormLayout($page),
            BlockType::SUMMER_BANNER => new SummerBlockFormLayout($page),
        };
    }
}