<?php

declare(strict_types=1);

class SectionRenderer
{
    private ?FieldRenderer $fieldRenderer = null;
    private ?FieldGroupRenderer $fieldGroupRenderer = null;
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

    public function render(string $sectionKey, FormBuilder $form, array $sectionsConfig, AbstractForm $formInstance): AbstractHtmlComponent
    {
        if (!isset($sectionsConfig[$sectionKey])) {
            throw new InvalidArgumentException("Section '$sectionKey' is not defined.");
        }

        $sectionContent = $sectionsConfig[$sectionKey];
        $fields = $this->renderSectionContent($sectionContent, $form, $formInstance);

        $sectionClass = 'frm-section ' . $sectionKey;
        $extraClass = $formInstance->getSectionExtraClass($sectionKey);
        $sectionTitle = $formInstance->getSectionTitle($sectionKey);

        return $form->tag('div')
            ->class($sectionClass)
            ->add(
                $form->tag('h4')
                    ->class('frm-section__title' . $extraClass)
                    ->content($sectionTitle),
                $form->tag('div')
                    ->class('frm-section__body' . $extraClass)
                    ->add(...$fields),
            );
    }

    private function renderSectionContent(array $sectionContent, FormBuilder $form, AbstractForm $formInstance): array
    {
        $elements = [];
        $groupIndex = 0;

        foreach ($sectionContent as $item) {
            if (isset($item['type']) && $item['type'] === 'field-group') {
                // Process field group with indexing
                $processedGroup = $this->processFieldGroupWithIndexing($item, $groupIndex);
                $elements[] = $this->fieldGroupRenderer->renderFieldGroup($processedGroup, $form, $formInstance);
                $groupIndex++;
            } else {
                // Add global index to regular fields too
                $item['_globalIndex'] = $this->globalFieldIndex++;
                $elements[] = $this->fieldRenderer->render($item, $form, $formInstance);
            }
        }

        return $elements;
    }

    private function hasFieldGroups(array $sectionContent): bool
    {
        foreach ($sectionContent as $item) {
            if (isset($item['type']) && $item['type'] === 'field-group') {
                return true;
            }
        }
        return false;
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

    public function reset(): void
    {
        $this->globalFieldIndex = 0;
    }
}