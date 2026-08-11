<?php

declare(strict_types=1);

final class ContentBlockFormFactory
{
    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
        private IconBuilder $iconBuilder,
        private readonly FormSectionHeader $header,
        private FileMetadataService $metadataService,
        private PageSectionOptionsService $optionsService,
        private ContentBlockFormLayoutFactory $layoutFactory,
    ) {
    }

    public function create(
        BlockType $type,
    ): AbstractFormConfigFactory {
        return match ($type) {
            BlockType::HERO => new HeroSectionFormConfigFactory(
                $this->iconBuilder,
                $this->metadataService,
                new HeroSectionConfigBuilder(
                    $this->optionsService,
                ),
            ),
            BlockType::SMALL_BANNER,
            BlockType::BIG_BANNER,
            BlockType::SUMMER_BANNER => new ContentBlockFormConfigFactory(
                $this->htmlBuilder,
                $this->iconBuilder,
                $this->header,
                $this->optionsService,
                $type,
                $this->layoutFactory,
                $this->metadataService,
            ),
        };
    }
}