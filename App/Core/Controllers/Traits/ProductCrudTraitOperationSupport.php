<?php

declare(strict_types=1);

trait ProductCrudTraitOperationSupport
{
    private function storeFormDataInSession(array $formData, UploadService $upload, array $errors, array $webPaths): void
    {
        $formValues = ArrayUtils::flattenWithKeys($formData);
        $fileMetadata = $upload->getFileMetadata();
        $fileMetadata = $this->mergeWebPathsIntoMetadata($fileMetadata, $webPaths);

        $this->flash->addFormInput(
            $this->request->getRequestedUri(),
            $formValues,
            $errors,
            $fileMetadata,
        );
    }

    private function prepareForValidation(array $data): array
    {
        // Filter out system fields
        $filteredData = ArrayUtils::filterSystemFields($data);

        // Ensure variations array has proper structure
        if (isset($filteredData['variations']) && is_array($filteredData['variations'])) {
            $filteredData['variations'] = $this->normalizeVariations($filteredData['variations']);
        }

        return $filteredData;
    }

    /**
     * Normalize variations structure to match validation rules.
     */
    private function normalizeVariations(array $variations): array
    {
        $normalized = [];

        foreach ($variations as $index => $variation) {
            if (is_array($variation)) {
                // Ensure all expected fields are present
                $normalized[$index] = array_merge([
                    'variant_type' => '',
                    'name' => '',
                    'sku' => '',
                    'price_modifier' => '',
                    'stock_quantity' => '',
                    'status' => '',
                    'attributes' => [],
                ], $variation);

                // Normalize attributes if present
                if (isset($variation['attributes']) && is_array($variation['attributes'])) {
                    $normalized[$index]['attributes'] = $this->normalizeAttributes($variation['attributes']);
                }
            }
        }

        return $normalized;
    }

    /**
     * Normalize attributes structure.
     */
    private function normalizeAttributes(array $attributes): array
    {
        $normalized = [];

        foreach ($attributes as $attrIndex => $attribute) {
            if (is_array($attribute)) {
                $normalized[$attrIndex] = array_merge([
                    'attribute_name' => '',
                    'attribute_value' => '',
                ], $attribute);
            }
        }

        return $normalized;
    }
}