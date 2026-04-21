<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;

final class HtmlFormSectionManager extends AbstractHtmlSectionManager
{
    public function __construct(
        private readonly VariationFieldMapping $variation,
        private readonly PriceRangesFieldMapping $range,
    ) {
    }

    public function getFieldMapping(array|Entity $formValues = []): array
    {
        $mapping = [];
        $this->id = $this->extractId($formValues);
        foreach ($this->sections as $section) {
            if ($section->shouldRender($formValues) && $section instanceof FieldSectionInterface) {
                $mapping = array_merge($mapping, $section->getFieldMapping());
            }
        }

        $variationMap = !$this->isEmpty($formValues, 'variation') ? $this->variation->getFieldMapping($formValues) : [];

        $priceRangeMap = !$this->isEmpty($formValues, 'price_ranges') ? $this->range->getFieldMapping($formValues) : [];

        $mapping = array_merge($mapping, $variationMap, $priceRangeMap);

        return array_filter($mapping, function ($sourcePath) {
            return !str_contains($sourcePath, '[');
        }, ARRAY_FILTER_USE_KEY);
    }

    public function getSections(array $formValues = []): array
    {
        $sections = [];
        foreach ($this->sections as $section) {
            if (!$section->shouldRender($formValues)) {
                continue;
            }

            $sections[$section->getKey()] = $section->getConfig($formValues);
        }

        $keys = array_keys($sections);
        if (in_array('variation', $keys, true)) {
            $sections['variation'] = $this->buildVariationSection($formValues);
        }

        if (in_array('price-range', $keys, true)) {
            $sections['price-range'] = $this->buildPriceRangeSection($formValues, $sections['price-range']);
        }
        return $sections;
    }

    private function isEmpty(array|Entity $formValues, string $key): bool
    {
        if ($formValues instanceof Entity && $formValues->isInitialized($key)) {
            return false;
        }
        if (is_array($formValues) && !empty($formValues[$key])) {
            return false;
        }

        return true;
    }

    private function buildVariationSection(array $formValues): array
    {
        $productId = $this->extractId($formValues);
        $isEdit = $productId !== null && $productId !== 0;
        $groups = $this->variation->buildGroups($isEdit, $formValues);

        return [
            [
                'type' => 'field-group',
                'wrapperClass' => 'variation-group span-all',
                'content' => $groups,
            ],
        ];
    }

    private function buildPriceRangeSection(array $formValues, array $currentConfig): array
    {
        $categoryId = $this->extractId($formValues);
        $isEdit = $categoryId !== null && $categoryId !== 0;

        // Get dynamic bracket fields if we have existing data
        $dynamicBrackets = $this->range->buildGroups($isEdit, $formValues);

        if (!empty($dynamicBrackets)) {
            $globalFields = array_slice($currentConfig, 0, 2);
            return array_merge($globalFields, $dynamicBrackets);
        }

        // No dynamic data, return original static config
        return $currentConfig;
    }

    private function extractId(array|Entity $formValues): null|string|int
    {
        if (isset($this->id)) {
            return $this->id;
        }
        if ($formValues instanceof Entity) {
            $id = $formValues->getEntityPrimarykeyValue();
            if ($id instanceof UuidInterface) {
                return $id->toString();
            }
            return $id ?? null;
        }
        return isset($formValues['public_id']) ? (int) $formValues['public_id'] : null;
    }
}