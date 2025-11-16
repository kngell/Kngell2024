<?php

declare(strict_types=1);

final class GeneralInformationSection extends BaseFormSection
{
    public function getKey(): string
    {
        return 'general-information';
    }

    public function getConfig(array $formValues = []): array
    {
        return [
            [
                'key' => 'id',
                'name' => 'public_id',
                'id' => 'public-id',
                'type' => 'hidden',
            ],
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