<?php

declare(strict_types=1);

final class ProductTagsSection extends BaseFieldSection
{
    use ProductFormSectionLayoutTrait;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private ProductTagOptionsService $tagsService,
        HtmlEscaper $escaper,
    ) {
        parent::__construct($builder, $iconBuilder, $escaper);
    }

    public function getKey(): string
    {
        return ProductSection::PRODUCT_TAGS->value;
    }

    public function getConfig(array $formValues = []): array
    {
        $options = $this->tagsService->getActiveOptions();
        return [
            [
                'key' => 'product-tags',
                'name' => 'product_tags[]',
                'map' => 'productTag.id',
                'label' => 'ProductTags',
                'type' => 'select',
                'options' => $options,
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
                'customComponent' => 'tagPreview',
                'attributes' => ['multiple' => true],
            ],
        ];
    }
}