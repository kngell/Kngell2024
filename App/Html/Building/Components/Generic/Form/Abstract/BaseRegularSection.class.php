<?php

declare(strict_types=1);

abstract class BaseRegularSection extends BaseFieldSection
{
    use SectionLayoutTrait;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        protected ?FormSectionHeader $header = null,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    final public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $this->formValues = $formValues;
        $configs = $this->normalizeConfigs($this->getSectionConfigs($formValues));

        return $this->buildFieldsFromConfigs($configs, $formValues);
    }

    final public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        $configs = $this->normalizeConfigs($this->getSectionConfigs());

        if (empty($configs)) {
            return null;
        }

        if (count($configs) === 1) {
            return $this->renderSingleConfigLayout($configs[0], $fields, $form);
        }

        // For multiple configs, render each as its own section
        return $this->renderMultipleConfigsLayout($configs, $fields, $form);
    }

    protected function normalizeConfigs(RegularSectionConfig|array $configs): array
    {
        return is_array($configs) ? $configs : [$configs];
    }

    protected function buildFieldsFromConfigs(array $configs, array $formValues = []): array
    {
        $fields = $this->getFieldsConfig($formValues) ?? [];

        foreach ($configs as $config) {
            $configFields = $config->getFields();
            if (!empty($configFields)) {
                $fields = array_merge($fields, $configFields);
            }
        }

        return $fields;
    }

    protected function renderSingleConfigLayout(RegularSectionConfig $config, array $fields, HtmlBuilder $form): AbstractHtmlComponent
    {
        $sectionClass = array_merge($config->getSectionClass(), $config->getWrapperClass());
        $sectionClassHeader = $config->getSectionClassHeader();
        $sectionClassBody = $config->getSectionClassBody();
        $id = $config->getSectionId();

        $section = $form->tag('div')->class(...$sectionClass);
        if ($id !== null) {
            $section->id($id);
        }

        $section->add(
            $this->header->getComponent(
                title: $config->getTitle(),
                wrapperClass: implode(' ', $sectionClassHeader),
                icon: $config->getIcon(),
                showRequired: $config->isShowRequired(),
            ),
        );

        $body = $form->tag('div')->class(...$sectionClassBody);

        // Apply layout using trait method
        $this->applyLayout($body, $fields, $form, $config);

        $section->add($body);
        return $section;
    }

    protected function renderMultipleConfigsLayout(array $configs, array $fields, HtmlBuilder $form): array
    {
        $layouts = [];
        $fieldIndex = 0;

        foreach ($configs as $config) {
            // Calculate how many fields this config owns
            $fieldsPerConfig = count($config->getFields());

            // Extract fields for this config
            $configFields = array_slice($fields, $fieldIndex, $fieldsPerConfig);
            $fieldIndex += $fieldsPerConfig;

            $layouts[] = $this->renderSingleConfigLayout($config, $configFields, $form);
        }
        $customLayout = $this->getCustomLayout($layouts, $form);
        if (!empty($customLayout)) {
            return !is_array($customLayout) ? [$customLayout] : $customLayout;
        }
        return $layouts;
    }

    protected function getSectionConfigs(array $formValues = []): RegularSectionConfig|array
    {
        $config = $this->getSectionConfig($formValues);
        return $config !== null ? $config : [];
    }

    /**
     * Legacy method for backward compatibility.
     * Override getSectionConfigs() instead.
     */
    protected function getSectionConfig(array $formValues = []): ?RegularSectionConfig
    {
        return null;
    }

    abstract protected function getFieldsConfig(array $formValues = []): array;
}