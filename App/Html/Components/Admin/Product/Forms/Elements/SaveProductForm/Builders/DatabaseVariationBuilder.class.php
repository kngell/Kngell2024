<?php

declare(strict_types=1);

final class DatabaseVariationBuilder implements VariationBuilderInterface
{
    public function __construct(
        private readonly ProductVariationModel $variationModel,
        private readonly VariationTypeModel $variationTypeModel,
        private readonly StockStatusModel $stockStatusModel,
    ) {
    }

    public function buildVariationGroups(bool $isEdit, $formValues): array
    {
        if (!$isEdit) {
            return $this->buildVariationGroup(0, []);
        }

        $productId = $this->extractProductId($formValues);
        if (!$productId) {
            return $this->buildVariationGroup(0, []);
        }

        $variations = $this->variationModel->find($productId)->asArray();
        if (empty($variations)) {
            return $this->buildVariationGroup(0, []);
        }

        $groups = [];
        foreach ($variations as $index => $variation) {
            $groups = array_merge($groups, $this->buildVariationGroup($index, $variation));
        }

        return $groups;
    }

    public function getBaseVariationGroup(): array
    {
        return [
            [
                'key' => 'variant-type',
                'name' => 'variations[{i}][variation_type_id]',
                'label' => 'Variation Type',
                'type' => 'select',
                'class' => 'span-all',
                'options' => $this->getVariationTypeOptions(),
                'suffixIcon' => 'icon-arrow-down',
                'disabled' => false,
            ],
            [
                'key' => 'variation-id',
                'name' => 'variations[{i}][id]',
                'type' => 'hidden',
            ],
            [
                'key' => 'variation-name',
                'name' => 'variations[{i}][name]',
                'label' => 'Variation Name',
                'placeholder' => 'Large, Red, Cotton...',
                'type' => 'text',
                'class' => 'span-all',
            ],
            [
                'key' => 'variation-sku',
                'name' => 'variations[{i}][variation_sku]',
                'label' => 'Variation SKU',
                'placeholder' => 'TSHIRT-RED-L',
                'type' => 'text',
            ],
            [
                'key' => 'price-modifier',
                'name' => 'variations[{i}][price_modifier]',
                'label' => 'Price Modifier',
                'placeholder' => '+5.00 or -2.50',
                'type' => 'number',
                'step' => '0.01',
            ],
            [
                'key' => 'variation-stock-quantity',
                'name' => 'variations[{i}][stock_quantity]',
                'label' => 'Variation Stock quantity',
                'placeholder' => '0',
                'type' => 'number',
                'step' => '1',
            ],
            [
                'key' => 'variation-status',
                'name' => 'variations[{i}][variation_status]',
                'label' => 'Variation Status',
                'type' => 'select',
                'options' => $this->getVariationStatusOptions(),
                'suffixIcon' => 'icon-arrow-down',
            ],
            [
                'type' => 'field-group',
                'wrapperClass' => 'variation-attributes',
                'content' => [
                    [
                        'key' => 'attribute-id',
                        'name' => 'variations[{i}][attributes][0][id]',
                        'type' => 'hidden',
                    ],
                    [
                        'key' => 'attribute-name',
                        'name' => 'variations[{i}][attributes][0][attribute_name]',
                        'label' => 'Attribute Name',
                        'placeholder' => 'color...',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'attribute-value',
                        'name' => 'variations[{i}][attributes][0][attribute_value]',
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
        ];
    }

    public function getFieldMapping(array $formValues = []): array
    {
        $sourcePrefix = 'product_variation_show';

        return [
            "{$sourcePrefix}.*.variation_type.id" => 'variations.*.variation_type_id',
            "{$sourcePrefix}.*.id" => 'variations.*.id',
            "{$sourcePrefix}.*.name" => 'variations.*.name',
            "{$sourcePrefix}.*.variation_sku" => 'variations.*.variation_sku',
            "{$sourcePrefix}.*.price_modifier" => 'variations.*.price_modifier',
            "{$sourcePrefix}.*.stock_quantity" => 'variations.*.stock_quantity',
            "{$sourcePrefix}.*.variation_status" => 'variations.*.variation_status',
            "{$sourcePrefix}.*.variation_attribute.*.id" => 'variations.*.attributes.*.id',
            "{$sourcePrefix}.*.variation_attribute.*.attribute_name" => 'variations.*.attributes.*.attribute_name',
            "{$sourcePrefix}.*.variation_attribute.*.attribute_value" => 'variations.*.attributes.*.attribute_value',
        ];
    }

    private function buildVariationGroup(int $index, array $data): array
    {
        $fields = $this->getBaseVariationGroup();
        $this->processFieldGroup($fields, $index, $data);
        return $fields;
    }

    private function processFieldGroup(array &$fields, int $index, array $data): void
    {
        foreach ($fields as &$field) {
            $search = '{i}';
            $replace = (string) $index;

            // 1. Fix the HTML Name for the Browser
            if (isset($field['name'])) {
                $field['name'] = str_replace($search, $replace, $field['name']);
            }

            if (isset($field['name'])) {
                $field['data_path'] = str_replace(['[', ']'], ['.', ''], rtrim($field['name'], ']'));
            }

            if (isset($field['ariaLabel'])) {
                $field['ariaLabel'] = str_replace($search, (string) ($index + 1), $field['ariaLabel']);
            }

            if (isset($field['attributes']['data-group-id'])) {
                $field['attributes']['data-group-id'] = str_replace('{i}', (string) ($index + 1), $field['attributes']['data-group-id']);
            }

            if (!empty($field['key']) && isset($data[$field['key']])) {
                $field['value'] = $data[$field['key']];
            }

            if (isset($field['content']) && is_array($field['content'])) {
                $this->processFieldGroup($field['content'], $index, $data);
            }
        }
    }

    private function getVariationStatusOptions(): array
    {
        $options = ['' => '-- Select Status --'];
        foreach (ProductVariationStatus::cases() as $case) {
            $options[ucfirst($case->value)] = ucfirst($case->value); // 'active' => 'Active'
        }
        return $options;
    }

    private function getVariationTypeOptions(): array
    {
        try {
            $entities = $this->variationTypeModel->all()->asClass();
            return $this->buildOptions($entities, '-- Select Variation Type --');
        } catch (QueryResultException $e) {
            error_log('VariationType options load failed: ' . $e->getMessage());
            return ['' => '-- Error Loading Variation Types --'];
        }
    }

    private function extractProductId($formValues): ?int
    {
        if ($formValues instanceof Product) {
            return $formValues->getId();
        }
        return $formValues['id'] ?? null;
    }

    private function buildOptions(array $entities, string $defaultLabel): array
    {
        $options = ['' => $defaultLabel];
        foreach ($entities as $entity) {
            if ($entity instanceof VariationType) {
                $options[$entity->getId()] = ucwords($entity->getName());
            } elseif (is_object($entity)) {
                $options[$entity->id ?? ''] = $entity->name ?? 'Unknown';
            }
        }
        return $options;
    }
}
