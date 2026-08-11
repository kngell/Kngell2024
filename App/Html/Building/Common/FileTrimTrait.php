<?php

declare(strict_types=1);
trait FileTrimTrait
{
    /**
     * Extract base field name from indexed field name
     * Examples:
     * - "main_image[0]" -> "main_image"
     * - "img_gallery[1]" -> "img_gallery"
     * - "main_image[]" -> "main_image"
     * - "main_video" -> "main_video".
     */
    protected function getBaseFieldName(string $fieldName): string
    {
        // Then remove array index pattern like [0], [1], [123]
        return preg_replace('/\[\d+\]$/', '', $fieldName);
    }

    protected function fieldHasValidationErrors(string $fieldName, array $fieldErrors): bool
    {
        $baseFieldName = $this->getBaseFieldName($fieldName);

        return isset($fieldErrors[$fieldName])
            || isset($fieldErrors[$baseFieldName])
            || isset($fieldErrors[rtrim($fieldName, '[]')])
            || isset($fieldErrors[$baseFieldName . '[]']);
    }

    private function flattenBlockMetadata(array $data): array
    {
        $result = [];

        // Copy all non-block_metadata fields as-is
        foreach ($data as $key => $value) {
            if ($key !== 'block_metadata') {
                $result[$key] = $value;
            }
        }

        // Flatten block_metadata recursively
        if (isset($data['block_metadata']) && is_array($data['block_metadata'])) {
            $this->flattenArray($data['block_metadata'], 'block_metadata', $result);
        }

        return $result;
    }

    private function flattenArray(array $array, string $prefix, array &$result): void
    {
        foreach ($array as $key => $value) {
            $newKey = $prefix . '[' . $key . ']';

            if (is_array($value)) {
                // Recursively flatten nested arrays
                $this->flattenArray($value, $newKey, $result);
            } else {
                // Wrap scalar values in an array to match original format
                $result[$newKey] = [$value];
            }
        }
    }
}