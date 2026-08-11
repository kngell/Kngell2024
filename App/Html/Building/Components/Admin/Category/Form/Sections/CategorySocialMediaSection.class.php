<?php

declare(strict_types=1);

class CategorySocialMediaSection extends BaseFieldSection
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
                'key' => 'meta-title',
                'name' => 'meta_title',
                'type' => 'text',
                'label' => 'Meta Title',
                'maxlength' => 20,
                'counter' => '0/20',
                'footer' => [
                    'error' => '',
                ],
            ],
            [
                'key' => 'meta-descr',
                'name' => 'meta_description',
                'type' => 'textarea',
                'label' => 'Meta Description',
                'footer' => [
                    'error' => '',
                ],
            ],
            [
                'key' => 'meta-keyword',
                'name' => 'meta_keyword',
                'type' => 'text',
                'label' => 'Meta Keyword',
                'footer' => [
                    'error' => '',
                ],
            ],
            [
                'key' => 'twitter-card',
                'name' => 'twitter_card',
                'type' => 'text',
                'label' => 'Twitter Card',
                'footer' => [
                    'error' => '',
                ],
            ],
        ];
    }

    public function getKey(): string
    {
        return CategorySection::SOCIAL_MEDIA->value;
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): ?AbstractHtmlComponent
    {
        $sectionClass = 'form-section';

        return $form->tag('div')
            ->class($sectionClass, 'social-media')
            ->add(
                $this->header->getComponent(
                    title: 'SEO & Social Media',
                    wrapperClass: $sectionClass . '__header',
                    showRequired: false,
                ),
                $form->tag('div')
                    ->class($sectionClass . '__body')
                    ->add(
                        ...$fields,
                    ),
            );
    }
}