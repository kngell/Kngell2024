<?php

declare(strict_types=1);

final class ShippingSection extends BaseFieldSection
{
    public function getKey(): string
    {
        return 'shipping';
    }

    public function getConfig(array $formValues = []): array
    {
        return [
            [
                'key' => 'is-physical-product',
                'name' => 'is-physical-product',
                'label' => 'This is a physical
                                         product',
                'type' => 'checkbox',
                'class' => ['span-all', 'blue-check'],
            ],
            [
                'key' => 'weight',
                'name' => 'weight',
                'label' => 'Weight',
                'type' => 'text',
                'placeholder' => 'Product weight...',
            ],
            [
                'key' => 'height',
                'name' => 'height',
                'label' => 'Height',
                'type' => 'text',
                'placeholder' => 'Height (cm)...',
            ],
            [
                'key' => 'length',
                'name' => 'length',
                'label' => 'Length',
                'type' => 'text',
                'placeholder' => 'Length (cm)...',
            ],
            [
                'key' => 'width',
                'name' => 'width',
                'label' => 'width',
                'type' => 'text',
                'placeholder' => 'Width (cm)...',
            ],
        ];
    }
}