<?php

declare(strict_types=1);

class MimesValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue,
    ) {
    }

    public function validate(): array|string|bool
    {
        if ($this->isEmpty($this->inputValue)) {
            return false;
        }

        $files = is_array($this->inputValue) ? $this->inputValue : [$this->inputValue];
        $allowedTypes = array_map('trim', explode(',', $this->ruleValue));

        foreach ($files as $file) {
            if (!$this->isValidFile($file)) {
                continue;
            }

            $validationResult = $this->validateFileMimeType($file, $allowedTypes);

            if ($validationResult !== true) {
                return $validationResult;
            }
        }

        return false;
    }

    private function validateFileMimeType(mixed $file, array $allowedTypes): bool|string
    {
        $filename = $this->getFilename($file);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Method 1: Check by extension using MimeTypeConstants
        $extensionValid = $this->validateByExtension($extension, $allowedTypes);
        if ($extensionValid === true) {
            return true;
        }

        // Method 2: Check actual MIME type (more secure)
        $mimeTypeValid = $this->validateByActualMimeType($file, $allowedTypes);
        if ($mimeTypeValid === true) {
            return true;
        }

        // Return appropriate error
        if ($extensionValid !== false) {
            return $extensionValid; // Returns the error message
        }

        return $this->errorMessage(
            sprintf($this->errorParams['message'], $this->display, $extension, $this->ruleValue),
            $this->errorParams['classes'],
        );
    }

    private function validateByExtension(string $extension, array $allowedTypes): bool|string
    {
        $mimeTypes = MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension] ?? [];

        if (empty($mimeTypes)) {
            return false; // Unknown extension
        }

        foreach ($allowedTypes as $allowedType) {
            // Check if allowed type is a main type (e.g., 'image', 'video')
            if ($this->isMainType($allowedType)) {
                foreach ($mimeTypes as $mimeType) {
                    if (str_starts_with($mimeType, $allowedType . '/')) {
                        return true;
                    }
                }
            }

            // Check if allowed type is a specific extension
            if (in_array($allowedType, $mimeTypes) || $extension === $allowedType) {
                return true;
            }
        }

        return false;
    }

    private function validateByActualMimeType(mixed $file, array $allowedTypes): bool
    {
        $mimeType = $this->getMimeType($file);

        if (empty($mimeType)) {
            return false;
        }

        foreach ($allowedTypes as $allowedType) {
            if ($this->isMainType($allowedType)) {
                // Check main type (e.g., 'image' matches 'image/jpeg')
                if (str_starts_with($mimeType, $allowedType . '/')) {
                    return true;
                }
            } else {
                // Check specific MIME type or extension
                $allowedMimeTypes = MimeTypeConstants::MIME_TYPE_TO_EXTENSION[$allowedType] ?? [];
                if (in_array($mimeType, array_keys($allowedMimeTypes))) {
                    return true;
                }

                // Also check if the allowed type matches the extension
                $extension = strtolower($allowedType);
                if ($extension === strtolower(pathinfo($this->getFilename($file), PATHINFO_EXTENSION))) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isMainType(string $type): bool
    {
        $mainTypes = ['image', 'video', 'audio', 'text', 'application', 'font', 'model'];
        return in_array($type, $mainTypes);
    }

    private function isValidFile(mixed $file): bool
    {
        if ($file instanceof FileUpload) {
            return $file->isValid() && !$file->hasError();
        }

        return is_array($file) &&
               isset($file['name']) &&
               isset($file['size']) &&
               isset($file['type']) &&
               isset($file['tmp_name']) &&
               file_exists($file['tmp_name']);
    }

    private function getFilename(mixed $file): string
    {
        if ($file instanceof FileUpload) {
            return $file->getOriginalName();
        }

        return $file['name'] ?? '';
    }

    private function getMimeType(mixed $file): string
    {
        if ($file instanceof FileUpload) {
            return $file->getMimeType();
        }

        if (isset($file['tmp_name']) && file_exists($file['tmp_name'])) {
            return mime_content_type($file['tmp_name']) ?: $file['type'] ?? '';
        }

        return $file['type'] ?? '';
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [] || $value === '[]';
    }
}