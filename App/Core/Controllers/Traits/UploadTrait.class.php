<?php

declare(strict_types=1);
trait UploadTrait
{
    // /**
    //  * @return array<string>
    //  */
    // private function getWebPathsFromFormData(array $formData): array
    // {
    //     $allWebPaths = [];
    //     foreach ($formData as $key => $value) {
    //         if (str_starts_with($key, 'web_path__')) {
    //             $values = is_array($value) ? $value : [$value];
    //             $allWebPaths = array_merge($allWebPaths, array_filter($values));
    //         }
    //     }
    //     return array_unique($allWebPaths);
    // }

    private function extractWebPathsFromForm(array &$formData): array
    {
        $webPaths = [];

        foreach ($formData as $key => $value) {
            if (str_starts_with($key, 'web_path__') && !empty($value)) {
                $fieldName = substr($key, 10);

                if (is_array($value)) {
                    $webPaths[$fieldName] = array_filter($value);
                } else {
                    $webPaths[$fieldName] = $value;
                }
                unset($formData[$key]);
            }
        }

        return $webPaths;
    }

    private function mergeWebPathsIntoMetadata(array $fileMetadata, array $webPaths): array
    {
        $result = [];

        // Convert all keys to clean field names (without brackets)
        foreach ($fileMetadata as $key => $value) {
            $cleanKey = rtrim($key, '[]');
            $result[$cleanKey] = $value;
        }

        // Now merge web paths (also using clean field names)
        foreach ($webPaths as $fieldName => $webPathData) {
            $cleanFieldName = rtrim($fieldName, '[]');
            $paths = is_array($webPathData) ? $webPathData : [$webPathData];

            // Initialize the field array if it doesn't exist
            if (!isset($result[$cleanFieldName])) {
                $result[$cleanFieldName] = [];
            }

            // Remove any existing web path entries for this field to avoid duplicates
            $result[$cleanFieldName] = array_filter($result[$cleanFieldName], function ($file) {
                return !($file['is_from_web_path'] ?? false);
            });

            // Add the correct web paths for this specific field
            foreach ($paths as $webPath) {
                if (empty($webPath)) {
                    continue;
                }

                $result[$cleanFieldName][] = [
                    'original_name' => basename($webPath),
                    'display_name' => pathinfo($webPath, PATHINFO_FILENAME),
                    'size' => 0,
                    'mime_type' => $this->guessMimeTypeFromPath($webPath),
                    'web_path' => $webPath,
                    'upload_infos' => [
                        'web_path' => $webPath,
                        'url' => $webPath,
                    ],
                    'metadata' => [
                        'mime_type' => $this->guessMimeTypeFromPath($webPath),
                        'is_image' => $this->isImagePath($webPath),
                        'has_error' => false,
                        'error' => null,
                    ],
                    'is_from_web_path' => true,
                    'intended_field' => $cleanFieldName, // Use clean field name
                ];
            }
        }

        // Ensure all expected fields exist
        $expectedFields = ['main_image', 'img_gallery', 'main_video'];
        foreach ($expectedFields as $field) {
            if (!isset($result[$field])) {
                $result[$field] = [];
            }
        }

        return $result;
    }

    // /**
    //  * Convert flat file metadata array to field-specific structure.
    //  */
    // private function convertToFieldSpecificStructure(array $flatMetadata): array
    // {
    //     $fieldSpecific = [];

    //     // Determine which field this file belongs to based on upload context
    //     // For now, assign to main_image as default (you might need more sophisticated logic)
    //     if (!empty($flatMetadata)) {
    //         $fieldSpecific['main_image'] = $flatMetadata;
    //     }

    //     return $fieldSpecific;
    // }

    // /**
    //  * Create file metadata structure from web paths.
    //  */
    // private function createMetadataFromWebPaths(array $webPaths): array
    // {
    //     $metadata = [];

    //     foreach ($webPaths as $fieldName => $webPathData) {
    //         // Handle both single paths and arrays of paths
    //         $paths = is_array($webPathData) ? $webPathData : [$webPathData];

    //         $fieldMetadata = [];
    //         foreach ($paths as $webPath) {
    //             if (empty($webPath)) {
    //                 continue;
    //             }

    //             $fieldMetadata[] = [
    //                 'original_name' => basename($webPath),
    //                 'display_name' => pathinfo($webPath, PATHINFO_FILENAME),
    //                 'size' => 0,
    //                 'mime_type' => $this->guessMimeTypeFromPath($webPath),
    //                 'web_path' => $webPath,
    //                 'upload_infos' => [
    //                     'web_path' => $webPath,
    //                     'url' => $webPath,
    //                 ],
    //                 'metadata' => [
    //                     'mime_type' => $this->guessMimeTypeFromPath($webPath),
    //                     'is_image' => $this->isImagePath($webPath),
    //                     'has_error' => false,
    //                     'error' => null,
    //                 ],
    //             ];
    //         }

    //         if (!empty($fieldMetadata)) {
    //             $metadata[$fieldName] = $fieldMetadata;
    //         }
    //     }

    //     return $metadata;
    // }

    // /**
    //  * Guess MIME type from file extension.
    //  */
    // private function guessMimeTypeFromPath(string $path): string
    // {
    //     $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    //     $mimeTypes = [
    //         'jpg' => 'image/jpeg',
    //         'jpeg' => 'image/jpeg',
    //         'png' => 'image/png',
    //         'gif' => 'image/gif',
    //         'webp' => 'image/webp',
    //         'svg' => 'image/svg+xml',
    //         'mp4' => 'video/mp4',
    //         'webm' => 'video/webm',
    //         'ogg' => 'video/ogg',
    //         'mov' => 'video/quicktime',
    //     ];

    //     return $mimeTypes[$extension] ?? 'application/octet-stream';
    // }

    // /**
    //  * Check if path appears to be an image.
    //  */
    // private function isImagePath(string $path): bool
    // {
    //     $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    //     $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    //     return in_array($extension, $imageExtensions);
    // }

    // private function extractFileMetadata(array $files): array
    // {
    //     $metadata = [];

    //     foreach ($files as $fieldName => $fileData) {
    //         if (is_array($fileData)) {
    //             $metadata[$fieldName] = [];
    //             foreach ($fileData as $fileUpload) {
    //                 if ($fileUpload instanceof FileUpload) {
    //                     $metadata[$fieldName][] = $this->extractSingleFileMetadata($fileUpload);
    //                 }
    //             }
    //         } elseif ($fileData instanceof FileUpload) {
    //             $metadata[$fieldName] = $this->extractSingleFileMetadata($fileData);
    //         }
    //     }

    //     return $metadata;
    // }

    // private function extractSingleFileMetadata(FileUpload $fileUpload): array
    // {
    //     $metadata = [
    //         'original_name' => $fileUpload->getOriginalName(),
    //         'size' => $fileUpload->getSize(),
    //         'error' => $fileUpload->getUploadError(),
    //         'error_description' => $fileUpload->getUploadErrorDescription(),
    //         'extension' => $fileUpload->getOriginalExtension(),
    //         'safe_filename' => $fileUpload->getSafeFilename(),
    //         'is_valid' => $fileUpload->isValid(),
    //         'has_error' => $fileUpload->hasError(),
    //         'uploaded' => $fileUpload->getUploadError() === ErrorFile::UPLOAD_ERR_OK,
    //         'is_safe' => $fileUpload->isSafeForUpload(),
    //     ];

    //     // Use the safe MIME type detection that won't throw exceptions
    //     $metadata['mime_type'] = $fileUpload->getMimeTypeSafe();

    //     // Use existing robust methods from FileUpload
    //     $metadata['is_image'] = $fileUpload->isImage();
    //     $metadata['is_video'] = $fileUpload->isVideo();
    //     $metadata['is_audio'] = $fileUpload->isAudio();
    //     $metadata['file_type_description'] = $fileUpload->getFileTypeDescription();

    //     // Additional metadata that might be useful for display
    //     $metadata['formatted_size'] = $fileUpload->getFormattedSize();
    //     $metadata['guess_extension'] = $fileUpload->guessExtension();

    //     return $metadata;
    // }
}