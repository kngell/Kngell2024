<?php

declare(strict_types=1);

final class GeneralInformationSection extends BaseFieldSection
{
    use ProductFormSectionLayoutTrait;

    public function getKey(): string
    {
        return ProductSection::GENERAL_INFOS->value;
    }

    public function getConfig(array $formValues = []): array
    {
        return [
            [
                'key' => 'name',
                'name' => 'name',
                'id' => 'product-name',
                'label' => 'Product Name',
                'placeholder' => 'Type product name here...',
                'type' => 'text',
            ],
            [
                'key' => 'short-description',
                'name' => 'short_description',
                'label' => 'Product Short Description',
                'placeholder' => 'Type short description here...',
                'type' => 'text',
            ],
            [
                'key' => 'description',
                'name' => 'description',
                'label' => 'Product Description',
                'placeholder' => 'Type product description here...',
                'type' => 'textarea',
            ],
        ];
    }
}