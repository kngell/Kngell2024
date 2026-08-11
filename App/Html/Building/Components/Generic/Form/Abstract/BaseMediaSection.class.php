<?php

declare(strict_types=1);

abstract class BaseMediaSection extends BaseFieldSection
{
    use SectionLayoutTrait;

    protected const string DROPZONE_TYPE = 'dropzone';
    protected const string TEXT_TYPE = 'text';

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        protected readonly FormSectionHeader $header,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $this->formValues = $formValues;
        $configs = $this->normalizeConfigs($this->getMediaConfigs());

        return $this->buildFieldsFromConfigs($configs);
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        $configs = $this->normalizeConfigs($this->getMediaConfigs());

        if (empty($configs)) {
            return null;
        }

        if (count($configs) === 1) {
            return $this->renderSingleConfigLayout($configs[0], $fields, $form);
        }

        // For multiple configs, render each as its own section
        return $this->renderMultipleConfigsLayout($configs, $fields, $form);
    }

    /**
     * Normalize configs to array.
     */
    protected function normalizeConfigs(MediaSectionConfig|array $configs): array
    {
        return is_array($configs) ? $configs : [$configs];
    }

    /**
     * Build fields array from configurations.
     */
    protected function buildFieldsFromConfigs(array $configs): array
    {
        $fields = [];

        foreach ($configs as $config) {
            $fields[] = $this->buildDropzoneField($config);

            if ($config->hasAltText()) {
                $fields[] = $this->buildAltTextField($config);
            }

            if (!empty($config->getCustomFields())) {
                $fields = array_merge($fields, $config->getCustomFields());
            }
        }

        return $fields;
    }

    /**
     * Render a single media config using the layout system.
     */
    protected function renderSingleConfigLayout(MediaSectionConfig $config, array $fields, HtmlBuilder $form): AbstractHtmlComponent
    {
        $sectionClass = array_merge($config->getSectionClass(), $config->getWrapperClass());
        $sectionClassHeader = $config->getHeaderSectionClass();
        $sectionClassBody = $config->getBodySectionClass();
        $id = $config->getSectionId();

        $section = $form->tag('div')->class(...$sectionClass);
        if ($id !== null) {
            $section->id($id);
        }

        $section->add(
            $this->header->getComponent(
                title: $config->getTitle(),
                wrapperClass: implode(' ', $sectionClassHeader),
                icon: $config->getIconTitle(),
                showRequired: $config->isShowRequired(),
            ),
        );

        $body = $form->tag('div')->class(...$sectionClassBody);

        // Apply layout using trait method
        $this->applyLayout($body, $fields, $form, $config);

        $section->add($body);
        return $section;
    }

    /**
     * Render multiple configs as separate sections.
     */
    protected function renderMultipleConfigsLayout(array $configs, array $fields, HtmlBuilder $form): array
    {
        $layouts = [];
        $fieldIndex = 0;

        foreach ($configs as $config) {
            // Calculate how many fields this config owns
            $fieldsPerConfig = 1; // The dropzone field itself
            if ($config->hasAltText()) {
                $fieldsPerConfig++;
            }
            $fieldsPerConfig += count($config->getCustomFields());

            // Extract fields for this config
            $configFields = array_slice($fields, $fieldIndex, $fieldsPerConfig);
            $fieldIndex += $fieldsPerConfig;

            // Render this config as a section (may have its own internal layout)
            $layouts[] = $this->renderSingleConfigLayout($config, $configFields, $form);
        }

        // Allow custom layout override
        $customLayout = $this->getCustomLayout($layouts, $form);
        if (!empty($customLayout)) {
            return !is_array($customLayout) ? [$customLayout] : $customLayout;
        }

        return $layouts;
    }

    /**
     * Build a single dropzone field from config.
     */
    protected function buildDropzoneField(MediaSectionConfig $config): array
    {
        return [
            'key' => $config->getKey(),
            'name' => $config->getDropzoneConfig()->getFieldName(),
            'type' => self::DROPZONE_TYPE,
            'dropzoneStyle' => $config->getDropzoneConfig()->getDropzoneStyle(),
            'multiple' => $config->getDropzoneConfig()->isMultiple(),
            'dragText' => $config->getDropzoneConfig()->getDragText(),
            'hintText' => $config->getDropzoneConfig()->getHintText(),
            'icon' => $config->getDropzoneConfig()->getIcon(),
            'footer' => $config->getFooter(),
        ];
    }

    /**
     * Build alt text field from config.
     */
    protected function buildAltTextField(MediaSectionConfig $config): array
    {
        return [
            'key' => $config->getAltTextName(),
            'name' => $config->getAltTextName(),
            'type' => self::TEXT_TYPE,
            'label' => $config->getAltTextLabel(),
            'placeholder' => ' ',
            'hint' => $config->getAltTextHint(),
        ];
    }

    /**
     * Get media configurations (implemented by child classes).
     * Can return a single config or an array of configs.
     */
    protected function getMediaConfigs(): MediaSectionConfig|array
    {
        $config = $this->getMediaConfig();
        return $config !== null ? $config : [];
    }

    /**
     * Legacy method for backward compatibility.
     * Override getMediaConfigs() instead.
     */
    abstract protected function getMediaConfig(): MediaSectionConfig|array;
}