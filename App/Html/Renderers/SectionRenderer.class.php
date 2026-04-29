<?php

declare(strict_types=1);

class SectionRenderer
{
    private ?FieldRenderer $fieldRenderer = null;
    private ?FieldGroupRenderer $fieldGroupRenderer = null;
    private ?DropzoneRenderer $dropzoneRenderer = null;
    private ?TableRenderer $tableRenderer = null;
    private int $globalFieldIndex = 0;

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

    public function render(
        string $sectionKey,
        HtmlBuilder $form,
        array $sectionsConfig,
        AbstractHtml $formInstance,
        ?HtmlSectionManagerInterface $sectionManager = null,
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
        $fields = $this->renderFormContent($sectionContent, $form, $formInstance);

        if ($section instanceof HtmlSectionInterface) {
            $customLayout = $section->getSectionLayout($fields, $sectionKey, $form);
            if ($customLayout !== null) {
                return $customLayout;
            }
        }

        return $formInstance->getFieldSectionLayout($fields, $sectionKey, $form);
    }

    public function reset(): void
    {
        $this->globalFieldIndex = 0;
    }

    /**
     * Delegate to the TableRenderer — single line, clean separation.
     */
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

    /**
     * Render regular form fields (unchanged).
     */
    private function renderFormContent(
        array $sectionContent,
        HtmlBuilder $builder,
        AbstractHtml $formInstance,
    ): array {
        $elements = [];
        $groupIndex = 0;
        $form = $builder->form();

        foreach ($sectionContent as $item) {
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
                );
            } else {
                $elements[] = $this->fieldRenderer->render($item, $form, $formInstance);
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