<?php

declare(strict_types=1);

final class ContentBlockModelFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected EntityFactoryInterface $factory,
        private ModelContextInterface $context,
        private ModelUtilityInterface $utils,
    ) {
    }

    public function createForType(BlockType $blockType): ContentBlockModel|ContentBlockShowModel
    {
        return match ($blockType) {
            BlockType::HERO,BlockType::BIG_BANNER,BlockType::SUMMER_BANNER => new ContentBlockModel(
                $this->em,
                $this->factory,
                $this->context,
                $this->utils,
            ),
            BlockType::SMALL_BANNER => new ContentBlockShowModel(
                $this->em,
                $this->factory,
                $this->context,
                $this->utils,
            ) ,
        };
    }
}