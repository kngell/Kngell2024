<?php

declare(strict_types=1);

enum UploadFileType: string
{
    public function getAllowedExtensions(): array
    {
        $extensions = [];

        foreach ($this->getAllowedMimeTypes() as $mimeType) {
            if (isset(MimeTypeConstants::MIME_TYPE_TO_EXTENSION[$mimeType])) {
                $extensions = array_merge($extensions, MimeTypeConstants::MIME_TYPE_TO_EXTENSION[$mimeType]);
            }
        }

        // Also get extensions from the reverse mapping
        foreach (MimeTypeConstants::EXTENSION_TO_MIME_TYPES as $extension => $mimeTypes) {
            foreach ($mimeTypes as $mimeType) {
                if (in_array($mimeType, $this->getAllowedMimeTypes())) {
                    $extensions[] = $extension;
                }
            }
        }

        return array_unique($extensions);
    }

    /**
     * Get allowed MIME types for this file type.
     */
    public function getAllowedMimeTypes(): array
    {
        return match($this) {
            self::IMAGE => array_filter(
                array_keys(MimeTypeConstants::MIME_TYPE_TO_EXTENSION),
                fn ($mime) => str_starts_with($mime, 'image/'),
            ),

            self::DOCUMENT => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.oasis.opendocument.text',
                'text/plain',
                'application/rtf',
                'text/richtext',
            ],

            self::VIDEO => array_filter(
                array_keys(MimeTypeConstants::MIME_TYPE_TO_EXTENSION),
                fn ($mime) => str_starts_with($mime, 'video/'),
            ),

            self::AUDIO => array_filter(
                array_keys(MimeTypeConstants::MIME_TYPE_TO_EXTENSION),
                fn ($mime) => str_starts_with($mime, 'audio/'),
            ),

            self::ARCHIVE => [
                'application/zip',
                'application/x-rar-compressed',
                'application/x-7z-compressed',
                'application/x-tar',
                'application/gzip',
                'application/x-bzip',
                'application/x-bzip2',
            ],

            self::SPREADSHEET => [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.oasis.opendocument.spreadsheet',
                'text/csv',
            ],

            self::PRESENTATION => [
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.oasis.opendocument.presentation',
            ],

            self::CODE => [
                'text/x-php',
                'application/javascript',
                'text/javascript',
                'text/html',
                'text/css',
                'application/json',
                'application/xml',
                'text/x-python',
                'text/x-java',
                'text/x-c',
                'text/x-c++',
                'text/x-ruby',
            ],

            self::FONT => array_filter(
                array_keys(MimeTypeConstants::MIME_TYPE_TO_EXTENSION),
                fn ($mime) => str_starts_with($mime, 'font/'),
            ),

            self::UNKNOWN => []
        };
    }

    /**
     * Check if a specific MIME type is allowed for this file type.
     */
    public function isMimeTypeAllowed(string $mimeType): bool
    {
        return in_array(strtolower($mimeType), $this->getAllowedMimeTypes());
    }

    /**
     * Check if a specific extension is allowed for this file type.
     */
    public function isExtensionAllowed(string $extension): bool
    {
        $extension = strtolower($extension);
        return in_array($extension, $this->getAllowedExtensions());
    }

    /**
     * Check if file is considered safe for upload.
     */
    public function isSafeForUpload(): bool
    {
        return match($this) {
            self::UNKNOWN => false,
            default => true
        };
    }

    /**
     * Get human-readable description.
     */
    public function getDescription(): string
    {
        return match($this) {
            self::IMAGE => 'Image File',
            self::DOCUMENT => 'Document',
            self::VIDEO => 'Video File',
            self::AUDIO => 'Audio File',
            self::ARCHIVE => 'Archive',
            self::SPREADSHEET => 'Spreadsheet',
            self::PRESENTATION => 'Presentation',
            self::CODE => 'Source Code',
            self::FONT => 'Font File',
            self::UNKNOWN => 'Unknown File Type'
        };
    }

    /**
     * Get example file extensions for this type (for UI/validation messages).
     */
    public function getExampleExtensions(): array
    {
        $allExtensions = $this->getAllowedExtensions();
        return array_slice($allExtensions, 0, 5); // Return first 5 as examples
    }

    /**
     * Detect file type from MIME type and extension.
     */
    public static function fromMimeAndExtension(string $mimeType, string $extension): self
    {
        $extension = strtolower($extension);
        $mimeType = strtolower($mimeType);

        // Use the comprehensive MIME type mapping
        return self::detectFromMimeType($mimeType)
            ?? self::detectFromExtension($extension)
            ?? self::UNKNOWN;
    }

    /**
     * Get file type from a FileUpload object.
     */
    public static function fromFileUpload(FileUpload $file): self
    {
        return self::fromMimeAndExtension(
            $file->getMimeType(),
            $file->getOriginalExtension(),
        );
    }

    /**
     * Detect file type primarily from MIME type.
     */
    private static function detectFromMimeType(string $mimeType): ?self
    {
        // Image types
        if (str_starts_with($mimeType, 'image/')) {
            return self::IMAGE;
        }

        // Video types
        if (str_starts_with($mimeType, 'video/')) {
            return self::VIDEO;
        }

        // Audio types
        if (str_starts_with($mimeType, 'audio/')) {
            return self::AUDIO;
        }

        // Font types
        if (str_starts_with($mimeType, 'font/')) {
            return self::FONT;
        }

        // Specific MIME type mappings
        return match($mimeType) {
            // Documents
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/rtf',
            'text/plain',
            'application/vnd.oasis.opendocument.text' => self::DOCUMENT,

            // Spreadsheets
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.spreadsheet' => self::SPREADSHEET,

            // Presentations
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.oasis.opendocument.presentation' => self::PRESENTATION,

            // Archives
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
            'application/x-tar',
            'application/gzip' => self::ARCHIVE,

            // Code files
            'text/x-php',
            'application/javascript',
            'text/html',
            'text/css',
            'application/json',
            'application/xml',
            'text/x-python' => self::CODE,

            default => null
        };
    }

    /**
     * Fallback detection from extension.
     */
    private static function detectFromExtension(string $extension): ?self
    {
        // Check if extension exists in MimeTypeConstants
        if (!isset(MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension])) {
            return null;
        }

        $mimeTypes = MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension];

        // Try to detect from the first MIME type associated with this extension
        foreach ($mimeTypes as $mimeType) {
            if ($detected = self::detectFromMimeType($mimeType)) {
                return $detected;
            }
        }

        return null;
    }
    case IMAGE = 'image';
    case DOCUMENT = 'document';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case ARCHIVE = 'archive';
    case SPREADSHEET = 'spreadsheet';
    case PRESENTATION = 'presentation';
    case CODE = 'code';
    case FONT = 'font';
    case UNKNOWN = 'unknown';
}