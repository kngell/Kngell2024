<?php

declare(strict_types=1);

trait UploadTrait
{
    private function extractWebPathsFromForm(array &$formData): array
    {
        $webPaths = [];

        foreach ($formData as $key => $value) {
            if (str_starts_with($key, 'web_path__') && !empty($value)) {
                $fieldName = substr($key, 10);
                $cleanFieldName = rtrim($fieldName, '[]');

                if (is_array($value)) {
                    $webPaths[$cleanFieldName] = array_filter($value);
                } else {
                    $webPaths[$cleanFieldName] = [$value];
                }

                unset($formData[$key]);
            }
        }

        return $webPaths;
    }

    private function mergeWebPathsIntoMetadata(array $fileMetadata, array $webPaths): array
    {
        $result = $fileMetadata;

        foreach ($webPaths as $fieldName => $webPathArray) {
            $cleanFieldName = rtrim($fieldName, '[]');

            // Initialize the field array if it doesn't exist
            if (!isset($result[$cleanFieldName])) {
                $result[$cleanFieldName] = [];
            }

            // Filter out existing web path entries to avoid duplicates
            $existingFiles = array_filter(
                $result[$cleanFieldName],
                fn ($file) => !($file['metadata']['is_temporary'] ?? false),
            );

            // Add metadata for each web path using the metadata service
            $webPathFiles = [];
            foreach ($webPathArray as $webPath) {
                if (empty($webPath)) {
                    continue;
                }

                $metadata = $this->metadataService->createFromWebPath($webPath);
                if ($metadata) {
                    $webPathFiles[] = $this->createWebPathMetadataEntry($metadata, $cleanFieldName);
                }
            }

            // Merge existing files with new web path files
            $result[$cleanFieldName] = array_merge($existingFiles, $webPathFiles);
        }

        return $result;
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
            'metadata' => $metadata, // Single source of truth
            'is_from_web_path' => true,
            'upload_infos' => [
                'web_path' => $metadata['web_path'],
                'url' => $metadata['url'],
                'filename' => $metadata['filename'],
                'file_type' => $metadata['file_type'],
                'is_image' => $metadata['is_image'],
            ],
        ];
    }

    /**
     * Extract display name from filename.
     */
    private function extractDisplayName(string $filename): string
    {
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $displayName = str_replace(['_', '-'], ' ', $baseName);
        return ucwords($displayName);
    }

    private function getFormFieldMetadata(array $uploadServices): array
    {
        $metadata = [];

        foreach ($uploadServices as $fieldName => $uploadService) {
            if ($uploadService instanceof FileUploadComponentInterface) {
                $cleanFieldName = rtrim($fieldName, '[]');
                $metadata[$cleanFieldName] = $uploadService->getFileMetadata();
            }
        }

        return $metadata;
    }

    private function processUploadedFiles(
        array $uploadServices,
        array $webPaths = [],
    ): array {
        $uploadedMetadata = $this->getFormFieldMetadata($uploadServices);

        if (!empty($webPaths)) {
            $uploadedMetadata = $this->mergeWebPathsIntoMetadata($uploadedMetadata, $webPaths);
        }

        return $uploadedMetadata;
    }

    private function storeFormDataInSessionWithMetadata(
        array $formData,
        array $uploadServices,
        array $errors = [],
        array $webPaths = [],
    ): void {
        $fileMetadata = $this->processUploadedFiles($uploadServices, $webPaths);

        // Store complete metadata
        $this->session->set('form_metadata', $fileMetadata);

        // Store errors
        if (!empty($errors)) {
            $this->session->set('form_errors', $errors);
        }

        // Store form data (without web path fields)
        $cleanFormData = $this->cleanFormData($formData);
        $this->session->set('form_data', $cleanFormData);
    }

    private function cleanFormData(array $formData): array
    {
        return array_filter($formData, function ($key) {
            return !str_starts_with($key, 'web_path__');
        }, ARRAY_FILTER_USE_KEY);
    }

    private function getStoredFormMetadata(): array
    {
        return $this->session->get('form_metadata') ?? [];
    }

    /**
     * Clear stored form data from session.
     */
    private function clearStoredFormData(): void
    {
        $this->session->delete('form_data');
        $this->session->delete('form_errors');
        $this->session->delete('form_metadata');
    }
}