<?php

declare(strict_types=1);

final class FormFactory
{
    public function __construct(
        private readonly HtmlFormSectionManager $sectionManager,
        private readonly FileMetadataService $metadataService,
        private readonly HtmlBuilder $builder,
        private readonly FlashRenderer $flashRenderer,
        private readonly IconBuilder $iconBuilder,
        private readonly ArrayFieldMapper $arrayFieldMapper,
    ) {
    }

    public function getForm(?FormConfig $formConfig = null): FormTemplateInterface
    {
        $provider = new FormSectionProvider(
            $formConfig,
            $this->iconBuilder,
        );

        return new MainFormHtmlElement(
            $formConfig,
            $this->metadataService,
            $this->builder,
            $this->flashRenderer,
            $this->iconBuilder,
            new ButtonBuilder($this->builder, $this->iconBuilder),
            $provider,
            $this->arrayFieldMapper,
            $this->sectionManager,
        );
    }
}