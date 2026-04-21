<?php

declare(strict_types=1);

class DisplaySettingsSection extends BaseFieldSection
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private readonly FormSectionHeader $header,
        private FormOptions $formOptions,
        private ToggleSwitch $toggleSwitch,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $this->formValues = $formValues;
        return [
            [
                'name' => 'small_banner_theme',
            ],
            [
                'name' => 'is_active',
            ],
        ];
    }

    public function getKey(): string
    {
        return 'display-settings';
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): ?AbstractHtmlComponent
    {
        $sectionClass = 'form-section';
        $defaultTheme = $this->formValues['small_banner_theme'] ?? null;
        if ($defaultTheme) {
            $this->formOptions->setDefaultOption($defaultTheme);
        }

        $toggleSwitch = $this->toggleSwitch
            ->wrapperClassPrev(['status-row__right'])
            ->build('is_active');

        $options = $this->formOptions
            ->addOption('Light Mode', 'Light theme for bright environments', ['data-option' => Theme::LIGHT->value])
            ->addOption('Dark Mode', 'Dark theme for reduced eye strain', ['data-option' => Theme::DARK->value])
            ->build('small_banner_theme');

        return $form->tag('div')->class($sectionClass, 'display-settings')->add(
            $this->header->getComponent(
                title: 'Display Settings',
                wrapperClass: $sectionClass . '__header',
                icon: 'icon-settings',
                showRequired: false,
            ),
            $form->tag('div')->class($sectionClass . '__body')->add(
                $options,
                $form->tag('div')->class('status-row')->add(
                    $form->tag('div')->class('status-row__left')->add(
                        $form->tag('p')->class('title')->content('Active Status'),
                        $form->tag('p')->class('descr')->content('Show Banner on site'),
                    ),
                    $toggleSwitch,
                ),
            ),
        );
    }
}