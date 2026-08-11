<?php

declare(strict_types=1);

final class PageFactory
{
    public function __construct(
        private readonly HtmlRegularSectionManager $sectionManager,
        private readonly HtmlBuilder $builder,
        private readonly IconBuilder $iconBuilder,
        private readonly ButtonBuilder $buttonBuilder,
        private readonly FlashRenderer $flashRenderer,
        private readonly FileMetadataService $metadataService,
    ) {
    }

    public function getPage(?PageConfig $pageConfig = null, array $entities = [], array $pagination = []): PageTemplateInterface
    {
        $provider = new PageSectionProvider(
            $pageConfig,
            $this->iconBuilder,
        );

        return new HtmlPageElement(
            $pageConfig,
            $provider,
            $this->sectionManager,
            $this->builder,
            $this->buttonBuilder,
            $this->iconBuilder,
            $this->flashRenderer,
            $this->metadataService,
            $entities,
            $pagination,
        );
    }
}