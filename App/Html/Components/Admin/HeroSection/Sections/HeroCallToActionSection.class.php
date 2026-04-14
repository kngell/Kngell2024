<?php

declare(strict_types=1);

class HeroCallToActionSection extends BaseFieldSection
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private readonly FormSectionHeader $header,
        private readonly ToggleSwitch $toggle,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getKey(): string
    {
        return 'call-to-action';
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        return [
            [
                'type' => 'field-group',
                'key' => 'primary_button',
                'wrapperClass' => 'column__content',
                'content' => [
                    [
                        'key' => 'primary_btn_text',
                        'name' => 'cta_text',
                        'type' => 'text',
                        'placeholder' => ' ',
                        'label' => 'Button Text',
                    ],
                    [
                        'key' => 'primary_btn_link',
                        'name' => 'cta_link',
                        'type' => 'text',
                        'placeholder' => ' ',
                        'label' => 'Button Link',
                    ],
                ],
            ],
            // Secondary button group
            [
                'type' => 'field-group',
                'key' => 'secondary_button',
                'wrapperClass' => 'column__content',
                'content' => [
                    [
                        'key' => 'secondary_btn_text',
                        'name' => 'cta_secondary_text',
                        'type' => 'text',
                        'placeholder' => ' ',
                        'label' => 'Button Text',
                    ],
                    [
                        'key' => 'secondary_btn_link',
                        'name' => 'cta_secondary_link',
                        'type' => 'text',
                        'placeholder' => ' ',
                        'label' => 'Button Link',
                    ],
                ],
            ],
        ];
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): ?AbstractHtmlComponent
    {
        $toggleSwitch = $this->toggle
          ->wrapperClassPrev(['column__title--toggle-switch'])
          ->build('is_active');

        return $form->tag('div')->class('form-section')
            ->add(
                $this->header->getComponent(
                    title: 'Call to Action',
                    showRequired:false,
                ),
                $form->tag('div')->class('form-section__body call-to-action')->add(
                    $form->tag('div')->class('column')->add(
                        $form->tag('div')->class('column__title')->add(
                            $form->tag('p')->class('column__title--text')->content('Primary Button'),
                            $form->tag('div')->class('column__title--toggle-switch'),
                        ),
                        $fields[0],
                    ),
                    // Secondary column
                    $form->tag('div')->class('column')->add(
                        $form->tag('div')->class('column__title')->add(
                            $form->tag('p')->class('column__title--text')->content('Secondary Button'),
                            $toggleSwitch,
                            // $form->tag('div')->class('column__title--toggle-switch')->add(
                            //     $form->input('checkbox')->id('toggle-1'),
                            //     $form->label()->class('toggle')->for('toggle-1')->add(
                            //         $form->tag('span')->class('track'),
                            //         $form->tag('span')->class('knob'),
                            //     ),
                            // ),
                        ),
                        $fields[1],
                    ),
                ),
            );
    }
}