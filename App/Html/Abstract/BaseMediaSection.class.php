<?php

declare(strict_types=1);

abstract class BaseMediaSection extends BaseFieldSection
{
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
        $config = $this->getMediaConfig();

        $fields = [];

        // Add dropzone field
        $fields[] = [
            'key' => $config->getDropzoneKey(),
            'name' => $config->getDropzoneName(),
            'type' => self::DROPZONE_TYPE,
            'dropzoneStyle' => $config->getDropzoneStyle(),
            'multiple' => $config->isMultiple(),
            'dragText' => $config->getDragText(),
            'hintText' => $config->getHintText(),
            'icon' => $config->getIcon(),
            'footer' => $config->getFooter(),
        ];

        if ($config->hasAltText()) {
            $fields[] = [
                'key' => 'alt_text',
                'name' => 'alt_text',
                'type' => self::TEXT_TYPE,
                'label' => $config->getAltTextLabel(),
                'placeholder' => ' ',
                'hint' => $config->getAltTextHint(),
            ];
        }

        if (!empty($config->getCustomFields())) {
            $fields = array_merge($fields, $config->getCustomFields());
        }

        return $fields;
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        $config = $this->getMediaConfig();
        $sectionClass = $config->getSectionClass();
        $wrapperClass = $config->getWrapperClass();

        return $form->tag('div')->class($sectionClass, $wrapperClass)->add(
            $this->header->getComponent(
                title: $config->getTitle(),
                wrapperClass: $sectionClass . '__header',
                icon: $config->getIconTitle(),
                showRequired: $config->showRequired(),
            ),
            $form->tag('div')->class($sectionClass . '__body')->add(
                ...$fields,
            ),
        );
    }

    abstract protected function getMediaConfig(): MediaSectionConfig;
}