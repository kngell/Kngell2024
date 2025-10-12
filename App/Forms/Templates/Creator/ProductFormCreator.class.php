<?php

declare(strict_types=1);

class ProductFormCreator extends AbstractFormCreator
{
    public function __construct(
        private HtmlBuilder $builder,
        private FieldRenderer $fieldRenderer,
        private FieldGroupRenderer $FieldGroupRenderer,
        protected SectionRenderer $sectionRenderer,
        private ButtonBuilder $buttonBuilder,
        private IconBuilder $iconBuilder,
        private FieldIdGenerator $idGenerator,
    ) {
    }

    public function create(string $action): ?FormTemplateInterface
    {
        return match (true) {
            StringUtils::containsAny($action, ['edit', 'new', 'update', 'create', 'destroy', 'add']) => new ProductForm($this->builder, $this->fieldRenderer, $this->FieldGroupRenderer, $this->sectionRenderer, $this->buttonBuilder, $this->iconBuilder, $this->idGenerator),
            $action === 'delete' => new ProductFormConfirmation($this->builder, $this->fieldRenderer, $this->sectionRenderer, $this->buttonBuilder, $this->iconBuilder),
            default => null,
        };
    }
}