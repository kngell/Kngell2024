<?php

declare(strict_types=1);

trait SectionLayoutTrait
{
    protected SectionLayout $layoutType = SectionLayout::LAYOUT_STANDARD;

    /**
     * Override to define which field indices go where
     * Example: return ['left' => [0, 1], 'right' => [2, 3, 4]];.
     */
    protected function getFieldIndicesMapping(): array
    {
        return [];
    }

    /**
     * Override to define custom row configurations with field indices
     * Example:
     * return [
     *     ['indices' => [0, 1], 'class' => 'form-row horizontal'],
     *     ['indices' => [2, 3, 4], 'class' => 'form-row vertical'],
     * ];.
     */
    protected function getRowIndicesConfig(): array
    {
        return [];
    }

    protected function buildStandardLayout(AbstractHtmlComponent $body, array $fields, HtmlBuilder $form, RegularSectionConfig|MediaSectionConfig $config): void
    {
        $body->add(...$fields);
    }

    protected function buildTwoColumnLayout(AbstractHtmlComponent $body, array $fields, HtmlBuilder $form, RegularSectionConfig|MediaSectionConfig $config): void
    {
        $mapping = $this->getFieldIndicesMapping();

        $leftIndices = $mapping['left'] ?? [];
        $rightIndices = $mapping['right'] ?? [];

        // Auto-split if no mapping provided
        if (empty($leftIndices) && empty($rightIndices)) {
            $midPoint = ceil(count($fields) / 2);
            $leftIndices = range(0, $midPoint - 1);
            $rightIndices = range($midPoint, count($fields) - 1);
        }

        $leftFields = [];
        foreach ($leftIndices as $index) {
            if (isset($fields[$index])) {
                $leftFields[] = $fields[$index];
            }
        }

        $rightFields = [];
        foreach ($rightIndices as $index) {
            if (isset($fields[$index])) {
                $rightFields[] = $fields[$index];
            }
        }

        $body->add(
            $form->tag('div')->class('form-row', 'horizontal')->add(
                $form->tag('div')->class('column')->add(...$leftFields),
                $form->tag('div')->class('column')->add(...$rightFields),
            ),
        );
    }

    protected function buildCustomRowsLayout(AbstractHtmlComponent $body, array $fields, HtmlBuilder $form, RegularSectionConfig|MediaSectionConfig $config): void
    {
        $rowConfig = $this->getRowIndicesConfig();

        if (empty($rowConfig)) {
            // Fallback to standard layout
            $this->buildStandardLayout($body, $fields, $form, $config);
            return;
        }

        $rowFieldHidden = [];
        foreach ($rowConfig as $row) {
            $indices = $row['indices'] ?? [];
            $rowClass = $row['class'] ?? ['form-row'];
            $title = $row['title'] ?? null;

            if ($title !== null) {
                $rowFields = [$form->tag('h4')->class('sub-section')->content($title)];
            } else {
                $rowFields = [];
            }

            foreach ($indices as $index) {
                if (isset($fields[$index])) {
                    $rowField = $fields[$index];
                    // Check if it's a HiddenType component
                    if ($rowField instanceof HiddenType) {
                        $rowFieldHidden[] = $rowField;
                    } else {
                        $rowFields[] = $rowField;
                    }
                }
            }

            if (!empty($rowFields)) {
                $body->add(
                    $form->tag('div')->class(...$rowClass)->add(...$rowFields),
                );
            }
        }

        if (!empty($rowFieldHidden)) {
            $body->add(...$rowFieldHidden);
        }
    }

    protected function buildCustomLayout(AbstractHtmlComponent $body, array $fields, HtmlBuilder $form, RegularSectionConfig|MediaSectionConfig $config): void
    {
        // Override in child classes for complete custom layouts
        $this->buildStandardLayout($body, $fields, $form, $config);
    }

    protected function createRowByIndices(HtmlBuilder $form, array $fields, array $indices, string $rowClass = 'form-row'): ?AbstractHtmlComponent
    {
        $rowFields = [];
        foreach ($indices as $index) {
            if (isset($fields[$index])) {
                $rowFields[] = $fields[$index];
            }
        }

        if (empty($rowFields)) {
            return null;
        }

        return $form->tag('div')->class($rowClass)->add(...$rowFields);
    }

    protected function getCustomLayout(array $sections, HtmlBuilder $builder): array|AbstractHtmlComponent
    {
        return [];
    }

    protected function applyLayout(AbstractHtmlComponent $body, array $fields, HtmlBuilder $form, RegularSectionConfig|MediaSectionConfig $config): void
    {
        switch ($this->layoutType) {
            case SectionLayout::LAYOUT_TWO_COLUMNS:
                $this->buildTwoColumnLayout($body, $fields, $form, $config);
                break;
            case SectionLayout::LAYOUT_CUSTOM_ROWS:
                $this->buildCustomRowsLayout($body, $fields, $form, $config);
                break;
            case SectionLayout::LAYOUT_CUSTOM:
                $this->buildCustomLayout($body, $fields, $form, $config);
                break;
            default:
                $this->buildStandardLayout($body, $fields, $form, $config);
        }
    }
}