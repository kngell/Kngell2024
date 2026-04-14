<?php

declare(strict_types=1);

class CategoryCanonicalInfoSection extends BaseFieldSection
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
                'key' => 'canonical-url',
                'name' => 'canonical_url',
                'type' => 'text',
                'label' => 'Cannonical image URL',
                'footer' => [
                    'error' => '',
                ],
            ],
            [
                'key' => 'og-url',
                'name' => 'og_image_url',
                'type' => 'text',
                'label' => 'Open Graph Image URL',
                'footer' => [
                    'error' => '',
                ],
            ],
        ];
    }

    public function getKey(): string
    {
        return 'canonical-infos';
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): ?AbstractHtmlComponent
    {
        $sectionClass = 'form-section';

        return $form->tag('div')
            ->class($sectionClass, 'canonical-image')
            ->add(
                $this->header->getComponent(
                    title: 'Image Urls',
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