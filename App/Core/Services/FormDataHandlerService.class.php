<?php

declare(strict_types=1);

class FormDataHandlerService implements FormDataHandlerInterface
{
    private const string WEB_PATH_PREFIX = 'web_path__';
    private const int WEB_PATH_PREFIX_LENGTH = 10;

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
            $this->flash->addFormInput(
                $requestUri,
                $formValues,
                $errors,
                $fileMetadata,
            );
        } catch (Throwable $e) {
            $this->flash->addFormInput(
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
            return $this->flash->flushForm($requestUri);
        } catch (Throwable $e) {
            throw new ControllerFormDataException('no Stored data found!');
        }
    }

    public function getOldInput(?string $key = null): mixed
    {
        return $this->flash->getOldInput($key);
    }

    public function hasStoredFormData(string $requestUri): bool
    {
        return false;
    }

    public function clearStoredFormData(string $requestUri): void
    {
        $this->flash->flushForm($requestUri);
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
            if ($this->isWebPathField($key) && !empty($value)) {
                $fieldName = substr($key, self::WEB_PATH_PREFIX_LENGTH);
                $cleanFieldName = rtrim($fieldName, '[]');
                $webPaths[$cleanFieldName] = $this->normalizeWebPathValue($value);
                unset($formData[$key]);
            }
        }

        return $webPaths;
    }

    public function prepareFormDataForView(array $formData, array $fileMetadata): array
    {
        $preparedData = $this->cleanFormData($formData);

        foreach ($fileMetadata as $fieldName => $files) {
            $paths = [];
            foreach ($files as $file) {
                if (($file['is_from_web_path'] ?? false) && isset($file['web_path'])) {
                    $paths[] = $file['web_path'];
                }
            }

            if (!empty($paths)) {
                $preparedData[self::WEB_PATH_PREFIX . $fieldName] =
                    count($paths) > 1 ? $paths : $paths[0];
            }
        }

        return $preparedData;
    }

    public function validateWebPaths(array $webPaths): array
    {
        $validated = [];

        foreach ($webPaths as $fieldName => $paths) {
            $validPaths = [];
            foreach ((array) $paths as $path) {
                if ($this->isValidWebPath($path)) {
                    $validPaths[] = $path;
                }
            }

            if (!empty($validPaths)) {
                $validated[$fieldName] = $validPaths;
            }
        }

        return $validated;
    }

    /**
     * @return FileMetadataService
     */
    public function getMetadataService(): FileMetadataService
    {
        return $this->metadataService;
    }

    private function mergeWebPathsIntoMetadata(array $fileMetadata, array $webPaths): array
    {
        $validatedPaths = $this->validateWebPaths($webPaths);

        foreach ($validatedPaths as $fieldName => $webPathArray) {
            $cleanFieldName = rtrim($fieldName, '[]');

            if (!isset($fileMetadata[$cleanFieldName])) {
                $fileMetadata[$cleanFieldName] = [];
            }

            // Remove existing web path entries to avoid duplicates
            $existingFiles = array_filter(
                $fileMetadata[$cleanFieldName],
                fn ($file) => !($file['is_from_web_path'] ?? false),
            );

            // Add new web path entries
            $webPathFiles = array_map(
                fn ($webPath) => $this->createWebPathMetadataEntryFromPath($webPath, $cleanFieldName),
                array_filter($webPathArray, fn ($path) => !empty($path)),
            );

            $fileMetadata[$cleanFieldName] = array_merge($existingFiles, $webPathFiles);
        }

        return $fileMetadata;
    }

    private function createWebPathMetadataEntryFromPath(string $webPath, string $fieldName): array
    {
        $metadata = $this->metadataService->createFromWebPath($webPath);

        if (!$metadata) {
            // Fallback for invalid paths
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

        return $this->createWebPathMetadataEntry($metadata, $fieldName);
    }

    private function createWebPathMetadataEntry(array $metadata, string $fieldName): array
    {
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
        return array_filter($formData, function ($key) {
            return !$this->isWebPathField($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    private function normalizeWebPathValue(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($v) => !empty($v)));
        }

        return !empty($value) ? [$value] : [];
    }

    private function isWebPathField(string $key): bool
    {
        return str_starts_with($key, self::WEB_PATH_PREFIX);
    }

    private function isValidWebPath(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Basic validation
        return str_starts_with($path, '/') &&
               strlen($path) > 1 &&
               !str_contains($path, '..'); // Prevent directory traversal
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