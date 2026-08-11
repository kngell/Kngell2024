<?php

declare(strict_types=1);

class FormDataHandlerService implements FormDataHandlerInterface
{
    private const int WEB_PATH_PREFIX_LENGTH = 10;
    private const array EXCLUDED_EMPTY_CHECK_KEYS = [
        'csrfToken',
        'frm_name',
        'form-tab',
    ];

    public function __construct(
        private FlashInterface $flash,
        private FileMetadataService $metadataService,
    ) {
    }

    public function storeFormData(
        array $formData,
        UploadService $upload,
        array $errors,
        array $webPaths,
        string $requestUri,
    ): void {
        try {
            $formValues = ArrayUtils::flattenWithKeys($formData);
            $fileMetadata = $upload->getFileMetadata();
            $fileMetadata = $this->mergeWebPathsIntoMetadata($fileMetadata, $webPaths);
            $this->flash->addFormData(
                $requestUri,
                $formValues,
                $errors,
                $fileMetadata,
            );
        } catch (Throwable $e) {
            $this->flash->addFormData(
                $requestUri,
                [],
                ['system' => ['An error occurred while saving form data. Please try again.']],
                [],
            );
        }
    }

    public function getStoredFormData(string $requestUri): array
    {
        try {
            return $this->flash->getFormData($requestUri);
        } catch (Throwable $e) {
            throw new ControllerFormDataException('no Stored data found!');
        }
    }

    public function isEmptyData(array $data, array $additionalExcludeKeys = []): bool
    {
        $excludeKeys = array_merge(self::EXCLUDED_EMPTY_CHECK_KEYS, $additionalExcludeKeys);
        $filtered = array_diff_key($data, array_flip($excludeKeys));
        $nonEmptyValues = array_filter($filtered, fn ($v) => $v !== '' && $v !== null);
        return empty($nonEmptyValues);
    }

    public function getFormData(?string $key = null): mixed
    {
        return $this->flash->getFormData($key);
    }

    public function hasStoredFormData(string $requestUri): bool
    {
        return false;
    }

    public function clearStoredFormData(string $requestUri): void
    {
        $this->flash->getFormData($requestUri);
    }

    public function prepareForValidation(array $data): array
    {
        $filteredData = ArrayUtils::filterSystemFields($data);

        if (isset($filteredData['variations']) && is_array($filteredData['variations'])) {
            $filteredData['variations'] = $this->normalizeVariations($filteredData['variations']);
        }

        return $filteredData;
    }

    public function extractWebPathsFromForm(array &$formData): array
    {
        $webPaths = [];

        foreach ($formData as $key => $value) {
            if (!str_starts_with((string) $key, FormValuesKeys::WEBPATH_PREFIX->value)) {
                continue;
            }

            $baseFieldName = substr($key, self::WEB_PATH_PREFIX_LENGTH);

            // Normalize the value to a consistent format
            $webPaths[$baseFieldName] = $this->normalizeWebPathValue($value);

            // Remove from original form data
            unset($formData[$key]);
        }

        return $webPaths;
    }

    public function prepareFormDataForView(array $formData, array $fileMetadata): array
    {
        $preparedData = $this->cleanFormData($formData);

        foreach ($fileMetadata as $fieldName => $files) {
            if (empty($files)) {
                continue;
            }

            $webPaths = [];
            foreach ($files as $file) {
                if (($file['is_from_web_path'] ?? false) && isset($file['web_path'])) {
                    $webPaths[] = $file['web_path'];
                }
            }

            if (empty($webPaths)) {
                continue;
            }

            // Store as array for multi-file, string for single file
            $preparedData[FormValuesKeys::WEBPATH_PREFIX->value . $fieldName] = count($webPaths) > 1
                ? $webPaths
                : $webPaths[0];
        }

        return $preparedData;
    }

    public function validateWebPaths(array $webPaths): array
    {
        $validated = [];

        foreach ($webPaths as $fieldName => $paths) {
            $pathsArray = is_array($paths) ? $paths : [$paths];
            $validPaths = [];

            foreach ($pathsArray as $path) {
                if ($this->isValidWebPath((string) $path)) {
                    $validPaths[] = $path;
                }
            }

            if (!empty($validPaths)) {
                // Preserve original structure (single vs multi)
                $validated[$fieldName] = is_array($paths) ? $validPaths : $validPaths[0];
            }
        }

        return $validated;
    }

    public function getMetadataService(): FileMetadataService
    {
        return $this->metadataService;
    }

    /**
     * Normalize web path value to consistent format
     * - Single string becomes string
     * - Array becomes filtered array (recursively)
     * - Empty becomes null.
     * - Handles nested structures like ['url' => ['nested' => [...]]].
     */
    private function normalizeWebPathValue(mixed $value): array|string|null
    {
        // Handle null or empty strings
        if ($value === null || $value === '') {
            return null;
        }

        // Handle scalar values
        if (!is_array($value)) {
            return (string) $value;
        }

        // Handle arrays recursively
        $result = [];
        foreach ($value as $key => $item) {
            $normalized = $this->normalizeWebPathValue($item);

            // Skip null/empty values
            if ($normalized === null) {
                continue;
            }

            // Preserve original keys (important for nested structures)
            if (is_numeric($key)) {
                // Numeric keys (indexed arrays)
                $result[] = $normalized;
            } else {
                // Associative keys (like 'url', 'image', etc.)
                $result[$key] = $normalized;
            }
        }

        // Re-index numeric arrays to ensure sequential keys
        if (!empty($result) && array_is_list($result)) {
            $result = array_values($result);
        }

        return empty($result) ? null : $result;
    }

    private function mergeWebPathsIntoMetadata(array $fileMetadata, array $webPaths): array
    {
        $validatedPaths = $this->validateWebPaths($webPaths);

        foreach ($validatedPaths as $fieldName => $webPathValue) {
            $cleanFieldName = rtrim($fieldName, '[]');

            if (!isset($fileMetadata[$cleanFieldName])) {
                $fileMetadata[$cleanFieldName] = [];
            }

            // Remove existing web path entries
            $existingFiles = array_filter(
                $fileMetadata[$cleanFieldName],
                fn ($file) => !($file['is_from_web_path'] ?? false),
            );

            // Create metadata entries for web paths
            $webPathsArray = is_array($webPathValue) ? $webPathValue : [$webPathValue];
            $webPathFiles = [];

            foreach ($webPathsArray as $webPath) {
                if (!empty($webPath)) {
                    $webPathFiles[] = $this->createWebPathMetadataEntry($webPath, $cleanFieldName);
                }
            }

            $fileMetadata[$cleanFieldName] = array_merge($existingFiles, $webPathFiles);
        }

        return $fileMetadata;
    }

    private function createWebPathMetadataEntry(string $webPath, string $fieldName): array
    {
        $metadata = $this->metadataService->createFromWebPath($webPath);

        if (!$metadata) {
            return [
                'original_name' => basename($webPath),
                'display_name' => pathinfo($webPath, PATHINFO_FILENAME),
                'size' => 0,
                'mime_type' => 'application/octet-stream',
                'web_path' => $webPath,
                'intended_field' => $fieldName,
                'metadata' => [
                    'is_temporary' => false,
                    'has_error' => true,
                    'is_image' => false,
                ],
                'is_from_web_path' => true,
                'has_error' => true,
                'error' => 'Invalid file path',
            ];
        }

        return [
            'original_name' => $metadata['filename'],
            'display_name' => $this->extractDisplayName($metadata['filename']),
            'size' => $metadata['size'],
            'mime_type' => $metadata['mime_type'],
            'web_path' => $metadata['web_path'],
            'intended_field' => $fieldName,
            'metadata' => $metadata,
            'is_from_web_path' => true,
            'has_error' => false,
            'error' => null,
        ];
    }

    private function extractDisplayName(string $filename): string
    {
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $displayName = str_replace(['_', '-'], ' ', $baseName);
        return ucwords($displayName);
    }

    private function cleanFormData(array $formData): array
    {
        return array_filter($formData, fn ($key) => !str_starts_with($key, FormValuesKeys::WEBPATH_PREFIX->value), ARRAY_FILTER_USE_KEY);
    }

    private function isValidWebPath(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return str_starts_with($path, '/') &&
               strlen($path) > 1 &&
               !str_contains($path, '..');
    }

    private function normalizeVariations(array $variations): array
    {
        $normalized = [];

        foreach ($variations as $index => $variation) {
            if (is_array($variation)) {
                $normalized[$index] = array_merge([
                    'variant_type' => '',
                    'name' => '',
                    'sku' => '',
                    'price_modifier' => '',
                    'stock_quantity' => '',
                    'status' => '',
                    'attributes' => [],
                ], $variation);

                if (isset($variation['attributes']) && is_array($variation['attributes'])) {
                    $normalized[$index]['attributes'] = $this->normalizeAttributes($variation['attributes']);
                }
            }
        }

        return $normalized;
    }

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