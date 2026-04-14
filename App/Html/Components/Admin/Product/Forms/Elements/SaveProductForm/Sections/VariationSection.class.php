<?php

declare(strict_types=1);

final class VariationSection extends BaseFieldSection
{
    public function getKey(): string
    {
        return 'variation';
    }

    public function getConfig(array $formValues = []): array
    {
        return[
            [
                'type' => 'field-group',
                'wrapperClass' => 'variation-group span-all',
                'content' => [
                    [
                        'key' => 'variant-type',
                        'name' => 'variations[0][variation_type_id]',
                        'label' => 'Variation Type',
                        'type' => 'select',
                        'class' => 'span-all',
                        'options' => [],
                        'suffixIcon' => 'icon-arrow-down',
                        'disabled' => false,
                    ],
                    [
                        'key' => 'variation-name',
                        'name' => 'variations[0][name]',
                        'label' => 'Variation Name',
                        'placeholder' => 'Large, Red, Cotton...',
                        'type' => 'text',
                        'class' => 'span-all',
                    ],
                    [
                        'key' => 'variation-sku',
                        'name' => 'variations[0][variation_sku]',
                        'label' => 'Variation SKU',
                        'placeholder' => 'TSHIRT-RED-L',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'price-modifier',
                        'name' => 'variations[0][price_modifier]',
                        'label' => 'Price Modifier',
                        'placeholder' => '+5.00 or -2.50',
                        'type' => 'number',
                        'step' => '0.01',
                    ],
                    [
                        'key' => 'variation-stock-quantity',
                        'name' => 'variations[0][stock_quantity]',
                        'label' => 'Variation Stock quantity',
                        'placeholder' => '0',
                        'type' => 'number',
                        'step' => '1',
                    ],
                    [
                        'key' => 'variation-status',
                        'name' => 'variations[0][variation_status]',
                        'label' => 'Variation Status',
                        'type' => 'select',
                        'options' => [],
                        'suffixIcon' => 'icon-arrow-down',
                    ],
                    [
                        'type' => 'field-group',
                        'wrapperClass' => 'variation-attributes',
                        'content' => [
                            [
                                'key' => 'attribute-id',
                                'name' => 'variations[0][attributes][0][id]',
                                'type' => 'hidden',
                            ],
                            [
                                'key' => 'attribute-name',
                                'name' => 'variations[0][attributes][0][attribute_name]',
                                'label' => 'Attribute Name',
                                'placeholder' => 'color...',
                                'type' => 'text',
                            ],
                            [
                                'key' => 'attribute-value',
                                'name' => 'variations[0][attributes][0][attribute_value]',
                                'label' => 'Attribute Value',
                                'placeholder' => 'red...',
                                'type' => 'text',
                            ],
                        ],
                    ],
                    [
                        'type' => 'button-group',
                        'wrapperClass' => 'button-container span-all',
                        'content' => [
                            [
                                'type' => 'button',
                                'style' => 'secondary',
                                'size' => 'md',
                                'icon' => 'icon-plus',
                                'label' => 'Add Variation',
                                'attributes' => [
                                    'dataAction' => 'add-variation-group',
                                ],
                            ],
                            [
                                'type' => 'button',
                                'style' => 'danger-light',
                                'size' => 'md',
                                'class' => ['btn--icon-only'],
                                'icon' => 'icon-cancel',
                                'ariaLabel' => 'Remove Variation 1',
                                'attributes' => [
                                    'data-action' => 'remove-group',
                                    'data-group-id' => '1',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}