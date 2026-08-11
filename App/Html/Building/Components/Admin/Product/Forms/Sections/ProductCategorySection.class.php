<?php

declare(strict_types=1);

final class ProductCategorySection extends BaseFieldSection
{
    use ProductFormSectionLayoutTrait;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private CategoryOptionsService $categoryService,
        HtmlEscaper $escaper,
    ) {
        parent::__construct($builder, $iconBuilder, $escaper);
    }

    public function getKey(): string
    {
        return ProductSection::CATEGORY->value;
    }

    public function getConfig(array $formValues = []): array
    {
        $options = $this->categoryService->getActiveOptions();
        return [
            [
                'key' => 'category',
                'name' => 'category_id',
                'map' => 'category.id',
                'label' => 'Product Category',
                'type' => 'select',
                'options' => $options,
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
            ],
        ];
    }
}