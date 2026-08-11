<?php

declare(strict_types=1);

class SectionRenderer
{
    private int $globalFieldIndex = 0;

    public function __construct(
        private ?FieldRenderer $fieldRenderer = null,
        private ?FieldGroupRenderer $fieldGroupRenderer = null,
        private ?DropzoneRenderer $dropzoneRenderer = null,
        private ?TableRenderer $tableRenderer = null,
        private ?VariationGroupRenderer $variationGroupRenderer = null,
    ) {
    }

    public function fieldRenderer(FieldRenderer $fieldRenderer): self
    {
        $this->fieldRenderer = $fieldRenderer;
        return $this;
    }

    public function fieldGroupRenderer(FieldGroupRenderer $fieldGroupRenderer): self
    {
        $this->fieldGroupRenderer = $fieldGroupRenderer;
        return $this;
    }

    public function dropzoneRenderer(?DropzoneRenderer $dropzoneRenderer = null): self
    {
        $this->dropzoneRenderer = $dropzoneRenderer;
        return $this;
    }

    public function tableRenderer(?TableRenderer $tableRenderer = null): self
    {
        $this->tableRenderer = $tableRenderer;
        return $this;
    }

    public function variationGroupRenderer(?VariationGroupRenderer $variationGroupRenderer): self
    {
        $this->variationGroupRenderer = $variationGroupRenderer;
        return $this;
    }

    public function registerRenderer(FieldRenderer|FieldGroupRenderer|DropzoneRenderer|TableRenderer|VariationGroupRenderer ...$renderers): void
    {
        foreach ($renderers as $renderer) {
            match(true) {
                $renderer instanceof FieldRenderer => $this->fieldRenderer($renderer),
                $renderer instanceof FieldGroupRenderer => $this->fieldGroupRenderer($renderer),
                $renderer instanceof DropzoneRenderer => $this->dropzoneRenderer($renderer),
                $renderer instanceof TableRenderer => $this->tableRenderer($renderer),
                $renderer instanceof VariationGroupRenderer => $this->variationGroupRenderer($renderer),
            };
        }
    }

    public function render(
        string|int $sectionKey,
        HtmlBuilder $form,
        array $sectionsConfig,
        AbstractHtml $formInstance,
        ?HtmlSectionManagerInterface $sectionManager = null,
        null|FormLayoutInterface|PageLayoutInterface $formLayout = null,
        null|FormConfig|PageConfig $config = null,
    ): null|array|AbstractHtmlComponent {
        if (!isset($sectionsConfig[$sectionKey])) {
            throw new InvalidArgumentException("Section '$sectionKey' is not defined.");
        }

        $sectionContent = $sectionsConfig[$sectionKey];

        if ($sectionContent instanceof AbstractHtmlComponent) {
            return $sectionContent;
        }

        $section = $sectionManager?->getSection($sectionKey);

        if ($section instanceof TableSectionInterface) {
            return $this->renderTableSection($section, $sectionContent);
        }

        // Regular form section rendering...
        $fields = $this->renderFormContent($sectionContent, $form, $formInstance, $section, $config);

        if ($section instanceof HtmlSectionInterface) {
            $customLayout = $section->getSectionLayout($fields, $sectionKey, $form);
            if ($customLayout !== null) {
                return $customLayout;
            }
        }

        if (isset($sectionContent['type']) && $sectionContent['type'] === 'hidden') {
            if (count($fields) === 1) {
                return $fields[0];
            }
        }

        $fieldLayout = $formInstance->getFieldSectionLayout($fields, $sectionKey, $form) ?? $formLayout->getFieldSectionLayout($fields, $sectionKey, $form);

        if ($fieldLayout !== null) {
            return $fieldLayout;
        }
        if (!empty($fields)) {
            return $form->div()->add(...$fields);
        }
        return $fieldLayout;
    }

    public function reset(): void
    {
        $this->globalFieldIndex = 0;
    }

    private function renderTableSection(
        TableSectionInterface $section,
        mixed $config,
    ): AbstractHtmlComponent {
        if ($this->tableRenderer === null) {
            throw new LogicException(
                'TableRenderer not configured. Call tableRenderer() first.',
            );
        }

        return $this->tableRenderer->render(
            $section,
            $config,
        );
    }

    private function renderFormContent(
        array $sectionContent,
        HtmlBuilder $builder,
        AbstractHtml $formInstance,
        null|HtmlSectionInterface|HtmlGroupLayoutInterface $section = null,
        null|FormConfig|PageConfig $config = null,
    ): array {
        $elements = [];
        $groupIndex = 0;
        $form = $builder->form();

        if (ArrayUtils::isAssoc($sectionContent)) {
            $sectionContent = [$sectionContent];
        }
        foreach ($sectionContent as $item) {
            if ($item instanceof FormFieldConfig) {
                $item = $item->toArray();
            }

            if (!isset($item['type'])) {
                continue;
            }

            $item['_globalIndex'] = $this->globalFieldIndex++;

            if ($item['type'] === 'field-group') {
                $processedGroup = $this->processFieldGroupWithIndexing($item, $groupIndex);
                $elements[] = $this->fieldGroupRenderer->renderFieldGroup(
                    $processedGroup,
                    $form,
                    $formInstance,
                );
                $groupIndex++;
            } elseif ($item['type'] === 'dropzone' && $this->dropzoneRenderer) {
                $elements[] = $this->dropzoneRenderer->render(
                    $item,
                    $form,
                    $formInstance,
                    $item['_globalIndex'],
                    $config,
                );
            } elseif ($item['type'] === 'variation-group') {
                $elements = array_merge($elements, $this->variationGroupRenderer->render(
                    $item,
                    $form,
                    $formInstance,
                    $section,
                    $config,
                ));
            } else {
                $elements[] = $this->fieldRenderer->render($item, $form, $formInstance, $config);
            }
        }

        return $elements;
    }

    private function processFieldGroupWithIndexing(array $groupConfig, int $groupIndex): array
    {
        $processedGroup = $groupConfig;

        if (isset($processedGroup['content'])) {
            foreach ($processedGroup['content'] as $fieldIndex => &$item) {
                if (!isset($item['type']) || $item['type'] !== 'button') {
                    $item['_groupIndex'] = $groupIndex;
                    $item['_fieldIndex'] = $fieldIndex;
                    $item['_globalIndex'] = $this->globalFieldIndex++;
                    $item['_wrapperClass'] = $groupConfig['wrapperClass'] ?? '';
                }
            }
        }

        return $processedGroup;
    }
}