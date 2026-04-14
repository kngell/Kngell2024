<?php

declare(strict_types=1);

abstract class BaseRegularSection extends BaseFieldSection
{
    protected const string LAYOUT_STANDARD = 'standard';
    protected const string LAYOUT_TWO_COLUMNS = 'two-columns';
    protected const string LAYOUT_CUSTOM_ROWS = 'custom-rows';
    protected const string LAYOUT_CUSTOM = 'custom';

    protected string $layoutType = self::LAYOUT_STANDARD;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        protected readonly FormSectionHeader $header,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    final public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $this->formValues = $formValues;
        return $this->getFieldsConfig($formValues);
    }

    final public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        $config = $this->getSectionConfig();
        $sectionClass = $config->getSectionClass();

        $section = $form->tag('div')
            ->class($sectionClass, $config->getWrapperClass())
            ->add(
                $this->header->getComponent(
                    title: $config->getTitle(),
                    wrapperClass: $sectionClass . '__header',
                    icon: $config->getIcon(),
                    showRequired: $config->showRequired(),
                ),
            );

        $body = $form->tag('div')->class($sectionClass . '__body');

        // Apply layout based on type
        switch ($this->layoutType) {
            case self::LAYOUT_TWO_COLUMNS:
                $this->buildTwoColumnLayout($body, $fields, $form, $config);
                break;
            case self::LAYOUT_CUSTOM_ROWS:
                $this->buildCustomRowsLayout($body, $fields, $form, $config);
                break;
            case self::LAYOUT_CUSTOM:
                $this->buildCustomLayout($body, $fields, $form, $config);
                break;
            default:
                $this->buildStandardLayout($body, $fields, $form, $config);
        }

        $section->add($body);
        return $section;
    }

    abstract protected function getSectionConfig(): RegularSectionConfig;

    abstract protected function getFieldsConfig(array $formValues = []): array;

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

    protected function buildStandardLayout(AbstractHtmlComponent $body, array $fields, HtmlBuilder $form, RegularSectionConfig $config): void
    {
        $body->add(...$fields);
    }

    protected function buildTwoColumnLayout(AbstractHtmlComponent $body, array $fields, HtmlBuilder $form, RegularSectionConfig $config): void
    {
        $mapping = $this->getFieldIndicesMapping();

        $leftIndices = $mapping['left'] ?? [];
        $rightIndices = $mapping['right'] ?? [];

        // If no mapping provided, auto-split: first half left, second half right
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

    /**
     * Build layout with custom row configurations using field indices.
     */
    protected function buildCustomRowsLayout(AbstractHtmlComponent $body, array $fields, HtmlBuilder $form, RegularSectionConfig $config): void
    {
        $rowConfig = $this->getRowIndicesConfig();

        if (empty($rowConfig)) {
            // Fallback to standard layout
            $this->buildStandardLayout($body, $fields, $form, $config);
            return;
        }

        foreach ($rowConfig as $row) {
            $indices = $row['indices'] ?? [];
            $rowClass = $row['class'] ?? ['form-row'];

            $rowFields = [];
            foreach ($indices as $index) {
                if (isset($fields[$index])) {
                    $rowFields[] = $fields[$index];
                }
            }

            if (!empty($rowFields)) {
                $body->add(
                    $form->tag('div')->class(...$rowClass)->add(...$rowFields),
                );
            }
        }
    }

    protected function buildCustomLayout(AbstractHtmlComponent $body, array $fields, HtmlBuilder $form, RegularSectionConfig $config): void
    {
        // Override in child classes for complete custom layouts
        $this->buildStandardLayout($body, $fields, $form, $config);
    }

    /**
     * Helper method to create a row with specific fields by indices.
     */
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
}