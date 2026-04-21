<?php

declare(strict_types=1);

final class PriceRangesFieldMapping implements DatabaseArrayFieldMappingInterface
{
    private array $existingBrackets = [];

    public function buildGroups(bool $isEdit, array|Entity $formValues): array
    {
        $this->existingBrackets = $this->extractPriceRanges($formValues);

        if (empty($this->existingBrackets)) {
            return [];
        }

        return $this->generateBracketFields();
    }

    public function getBaseGroup(): array
    {
        return [
            [
                'key' => 'bracket-label',
                'name' => 'price_ranges[brackets][{i}][label]',
                'type' => 'text',
                'label' => 'Label',
                'placeholder' => ' ',
                'footer' => ['error' => ''],
            ],
            [
                'key' => 'bracket-min',
                'name' => 'price_ranges[brackets][{i}][min]',
                'type' => 'number',
                'label' => 'Min',
                'placeholder' => ' ',
                'step' => '0.01',
                'footer' => ['error' => ''],
            ],
            [
                'key' => 'bracket-max',
                'name' => 'price_ranges[brackets][{i}][max]',
                'type' => 'number',
                'label' => 'Max',
                'placeholder' => ' ',
                'step' => '0.01',
                'footer' => ['error' => ''],
            ],
        ];
    }

    public function getFieldMapping(array|Entity $formValues = []): array
    {
        return [
            'priceRanges.brackets.*.label' => 'price_ranges.brackets.*.label',
            'priceRanges.brackets.*.min' => 'price_ranges.brackets.*.min',
            'priceRanges.brackets.*.max' => 'price_ranges.brackets.*.max',
        ];
    }

    // public function getFieldMapping(array|Entity $formValues = []): array
    // {
    //     return [
    //         // priceRanges -> (Presenter returns ['brackets' => [...]]) -> brackets -> loop
    //         'priceRanges.brackets.*.label' => 'price_ranges.brackets.*.label',
    //         'priceRanges.brackets.*.min' => 'price_ranges.brackets.*.min',
    //         'priceRanges.brackets.*.max' => 'price_ranges.brackets.*.max',
    //     ];
    // }

    private function generateBracketFields(): array
    {
        $allBracketFields = [];

        foreach ($this->existingBrackets as $index => $bracket) {
            $fields = $this->getBaseGroup();

            foreach ($fields as &$field) {
                $field['name'] = str_replace('{i}', (string) $index, $field['name']);

                $key = str_replace('price_ranges[brackets][' . $index . ']', '', $field['name']);
                $key = trim($key, '[]');

                if ($key === 'label' && isset($bracket['label'])) {
                    $field['value'] = $bracket['label'];
                } elseif ($key === 'min' && isset($bracket['min'])) {
                    // Value is already cleaned
                    $field['value'] = $bracket['min'];
                } elseif ($key === 'max' && isset($bracket['max'])) {
                    $field['value'] = $bracket['max'];
                }
            }

            $allBracketFields = array_merge($allBracketFields, $fields);
        }

        return $allBracketFields;
    }

    private function extractPriceRanges(array|object $formValues): array
    {
        if ($formValues instanceof Category) {
            $priceRanges = $formValues->getPriceRanges();
            if ($priceRanges instanceof PriceRange) {
                $brackets = $priceRanges->getBrackets();
                $result = [];
                foreach ($brackets as $index => $bracket) {
                    $result[$index] = [
                        'label' => $bracket->getLabel(),
                        'min' => $bracket->getMin() ? (string) $bracket->getMin()->getAmount() : null,
                        'max' => $bracket->getMax() ? (string) $bracket->getMax()->getAmount() : null,
                    ];
                }
                return $result;
            }
            return [];
        }

        if (is_array($formValues)) {
            $brackets = [];
            foreach ($formValues as $key => $value) {
                if (preg_match('/price_ranges\[brackets\]\[(\d+)\]\[(\w+)\]/', $key, $matches)) {
                    $index = (int) $matches[1];
                    $field = $matches[2];

                    if (!isset($brackets[$index])) {
                        $brackets[$index] = [];
                    }
                    $cleanedValue = $this->cleanNumericValue($value);
                    $brackets[$index][$field] = $cleanedValue;
                    $formValues[$key] = $cleanedValue;
                }
            }

            // Sort by index and return
            ksort($brackets);
            return $brackets;
        }

        return [];
    }

    private function cleanNumericValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cleaned = preg_replace('/[^0-9,.-]/', '', $value);

        if (str_contains($cleaned, ',') && !str_contains($cleaned, '.')) {
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (str_contains($cleaned, '.') && str_contains($cleaned, ',')) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        }
        return (string) (float) $cleaned;
    }
}