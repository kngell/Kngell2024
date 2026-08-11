<?php

declare(strict_types=1);
final class VariationFieldMapping implements DatabaseArrayFieldMappingInterface
{
    public function __construct(
        private readonly ProductVariationModel $variationModel,
        private readonly VariationConfig $variationConfig,
    ) {
    }

    public function buildGroups(bool $isEdit, array|Entity $formValues): array
    {
        $hasTokenMismatch = $formValues[FormValuesKeys::TOKEN_MISMATCH->value] ?? null;

        if (!$isEdit && !$hasTokenMismatch) {
            return $this->variationConfig->buildVariationGroup(0, []);
        }

        $productId = $this->extractProductId($formValues);
        if (!$productId) {
            return $this->variationConfig->buildVariationGroup(0, []);
        }

        // Extract variations directly from formValues instead of querying database
        $variations = $this->extractVariationsFromFormValues($formValues);

        if (empty($variations)) {
            return $this->variationConfig->buildVariationGroup(0, []);
        }

        $groups = [];
        foreach ($variations as $index => $variation) {
            // Extract attributes for this variation from formValues
            $attributes = $this->extractAttributesForVariation($formValues, $variation['id'] ?? null);
            $variation['attributes'] = $attributes;

            $variationGroup = $this->variationConfig->buildVariationGroup($index, $variation);
            // $groups = array_merge($groups, $variationGroup);
            $groups = array_merge($groups, $variationGroup);
        }

        return $groups;
    }

    public function getBaseGroup(): array
    {
        return $this->variationConfig->getBaseConfig(0, [], false);
    }

    public function getFieldMapping(array|Entity $formValues = []): array
    {
        return $this->variationConfig->getFieldMapping();
    }

    private function extractVariationsFromFormValues(array|Entity $formValues): array
    {
        $variations = [];

        if ($formValues instanceof Product) {
            // If it's an entity, get the product variations
            if (method_exists($formValues, 'getProductVariationShow')) {
                $variationEntities = $formValues->getProductVariationShow();
                foreach ($variationEntities as $variationEntity) {
                    $variations[] = [
                        'id' => $variationEntity->getId(),
                        'product_id' => $formValues->getId(),
                        'variation_type_id' => $variationEntity->getVariationType()?->getId(),
                        'name' => $variationEntity->getName(),
                        'variation_sku' => $variationEntity->getVariationSku(),
                        'price_modifier' => $variationEntity->getPriceModifier(),
                        'stock_quantity' => $variationEntity->getStockQuantity(),
                        'stock_status_id' => $variationEntity->getStockStatus()?->getId(),
                        'variation_status' => $variationEntity->getVariationStatus()?->getStatusCode(),
                        'created_at' => $variationEntity->getCreatedAt(),
                        'updated_at' => $variationEntity->getUpdatedAt(),
                        'deleted_at' => $variationEntity->getDeletedAt(),
                    ];
                }
            }
        } elseif (is_array($formValues)) {
            // Extract variations from array format (like your dump shows)
            $variationIndex = 0;
            while (isset($formValues["variations[{$variationIndex}][id]"])) {
                $variation = [
                    'id' => $formValues["variations[{$variationIndex}][id]"] ?? null,
                    'variation_type_id' => $formValues["variations[{$variationIndex}][variation_type_id]"] ?? null,
                    'name' => $formValues["variations[{$variationIndex}][name]"] ?? null,
                    'variation_sku' => $formValues["variations[{$variationIndex}][variation_sku]"] ?? null,
                    'price_modifier' => $formValues["variations[{$variationIndex}][price_modifier]"] ?? null,
                    'stock_quantity' => $formValues["variations[{$variationIndex}][stock_quantity]"] ?? null,
                    'variation_status' => $formValues["variations[{$variationIndex}][variation_status]"] ?? null,
                ];

                // Only add if it has at least an ID or name
                if (!empty($variation['id']) || !empty($variation['name'])) {
                    $variations[] = $variation;
                }
                $variationIndex++;
            }
        }

        return $variations;
    }

    private function extractAttributesForVariation(array|Entity $formValues, null|int|string $variationId): array
    {
        $attributes = [];

        if ($formValues instanceof Entity) {
            // If it's an entity, get the variation attributes from the variation entity
            if ($variationId && method_exists($formValues, 'getProductVariationShow')) {
                $variations = $formValues->getProductVariationShow();
                foreach ($variations as $variation) {
                    if ($variation->getId() == $variationId) {
                        $attributeEntities = $variation->getVariationAttribute();
                        foreach ($attributeEntities as $attributeEntity) {
                            $attributes[] = [
                                'id' => $attributeEntity->getId(),
                                'attribute_name' => $attributeEntity->getAttributeName(),
                                'attribute_value' => $attributeEntity->getAttributeValue(),
                            ];
                        }
                        break;
                    }
                }
            }
        } elseif (is_array($formValues)) {
            // First, find which variation index matches the variationId
            $variationIndex = null;
            $varIndex = 0;
            while (isset($formValues["variations[{$varIndex}][id]"])) {
                if ($formValues["variations[{$varIndex}][id]"] == $variationId) {
                    $variationIndex = $varIndex;
                    break;
                }
                $varIndex++;
            }

            if ($variationIndex !== null) {
                // Extract attributes from the pattern: variations[0][attributes][0][id]
                $attrIndex = 0;
                while (isset($formValues["variations[{$variationIndex}][attributes][{$attrIndex}][id]"])) {
                    $attribute = [
                        'id' => $formValues["variations[{$variationIndex}][attributes][{$attrIndex}][id]"] ?? null,
                        'attribute_name' => $formValues["variations[{$variationIndex}][attributes][{$attrIndex}][attribute_name]"] ?? null,
                        'attribute_value' => $formValues["variations[{$variationIndex}][attributes][{$attrIndex}][attribute_value]"] ?? null,
                    ];

                    if (!empty($attribute['id']) || !empty($attribute['attribute_name'])) {
                        $attributes[] = $attribute;
                    }
                    $attrIndex++;
                }
            }
        }

        // Reindex sequentially if needed
        if (!empty($attributes) && !$this->isSequentiallyIndexed($attributes)) {
            $attributes = array_values($attributes);
        }

        return $attributes;
    }

    private function isSequentiallyIndexed(array $array): bool
    {
        $keys = array_keys($array);
        for ($i = 0; $i < count($keys); $i++) {
            if ($keys[$i] !== $i) {
                return false;
            }
        }
        return true;
    }

    private function extractProductId(array|Product $formValues): null|int|string
    {
        if ($formValues instanceof Product) {
            return $formValues->getId();
        }
        return $formValues['pdt_id'] ?? $formValues['id'] ?? null;
    }
}