<?php

declare(strict_types=1);

class CoreConfigurationSection extends BaseFieldSection
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private readonly FormSectionHeader $header,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        return [
            [
                'key' => 'sm_banner',
                'map' => 'id',
                'name' => 'sm_banner_id',
                'type' => 'hidden',
            ],
            [
                'key' => 'position',
                'name' => 'small_banner_class',
                'type' => 'select',
                'label' => 'Select Banner Class',
                'options' => SmallBannerClass::getOptions(),
                'required' => true,
                'rightIcon' => [
                    'icon' => 'icon-arrow-down',
                    'aria' => 'Arrow down',
                ],
                'footer' => ['hint' => 'xxx'],
            ],
            [
                'key' => 'page',
                'name' => 'page_target',
                'type' => 'text',
                'label' => 'Page Target',
                'placeholder' => ' ',
                'required' => true,
                'hint' => 'Where this banner should appear',
                // 'counter' => '0/255',
                'maxlength' => 255,
            ],
            [
                'key' => 'sort',
                'name' => 'sort_order',
                'type' => 'number',
                'label' => 'Sort Order',
                'placeholder' => ' ',
                'hint' => 'Sorting order for banners in the same position, lower numbers appear first',
                // 'counter' => '0/255',
                'maxlength' => 255,
            ],
        ];
    }

    public function getKey(): string
    {
        return 'core-configuration';
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): ?AbstractHtmlComponent
    {
        $sectionClass = 'core-configuration';

        return $form->tag('div')
            ->class($sectionClass)
            ->add(
                $this->header->getComponent(
                    title: 'Basic Information',
                    wrapperClass: $sectionClass . '__header',
                    showRequired: true,
                ),
                $form->tag('div')
                    ->class($sectionClass . '__body')
                    ->add(
                        $form->tag('div')->class('form-grid')->add(
                            $fields[0] ?? null,
                            $fields[1] ?? null,
                            $fields[2] ?? null,
                        ),
                        $fields[3] ?? null,
                    ),
            );
    }
}