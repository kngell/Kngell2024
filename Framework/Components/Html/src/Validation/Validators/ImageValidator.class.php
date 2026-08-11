<?php

declare(strict_types=1);

class ImageValidator extends AbstractValidator
{
    private const array UPLOAD_ERROR_MESSAGES = [
        UPLOAD_ERR_INI_SIZE => "The file '%s' exceeds upload_max_filesize limit",
        UPLOAD_ERR_FORM_SIZE => "The file '%s' exceeds form upload limit",
        UPLOAD_ERR_PARTIAL => "The file '%s' was partially uploaded",
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_CANT_WRITE => "The file '%s' could not be written on disk",
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary directory',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by PHP extension',
    ];

    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly array $rules, // Full rules array from config
    ) {
        parent::__construct();
    }

    public function validate(): array|string|bool
    {
        // Handle empty files (no file uploaded)
        if ($this->isEmpty($this->inputValue)) {
            // If required, this should be handled by RequiredValidator
            return false;
        }

        // For multiple file uploads (array input like main_image[])
        if (is_array($this->inputValue) && isset($this->inputValue['name']) && is_array($this->inputValue['name'])) {
            return $this->validateMultipleFiles();
        }

        // For single file upload
        return $this->validateSingleFile();
    }

    // Override isEmpty for file validation
    protected function isEmpty(mixed $value): bool
    {
        if (is_array($value)) {
            // For file uploads, check if any file was actually uploaded
            if (isset($value['error'])) {
                return $value['error'] === UPLOAD_ERR_NO_FILE;
            }
            if (isset($value['name']) && is_array($value['name'])) {
                foreach ($value['error'] ?? [] as $error) {
                    if ($error !== UPLOAD_ERR_NO_FILE) {
                        return false;
                    }
                }
                return true;
            }
        }

        return parent::isEmpty($value);
    }

    private function validateSingleFile(): array|string|bool
    {
        $fileData = $this->normalizeFileData($this->inputValue);

        // Check upload errors if rule specifies
        if (($this->rules['upload_error'] ?? false) === true) {
            $uploadError = $this->checkUploadErrors($fileData);
            if ($uploadError !== false) {
                return $uploadError;
            }
        }

        // Check if required
        if (($this->rules['required'] ?? false) === true ||
            !empty($this->rules['required_if'])) {
            if ($fileData['error'] === UPLOAD_ERR_NO_FILE) {
                return $this->buildErrorMessage('required', $this->display . ' is required');
            }
        }

        // Skip further validation if no file was uploaded (optional field)
        if ($fileData['error'] === UPLOAD_ERR_NO_FILE) {
            return false;
        }

        // Check file size limits
        $sizeError = $this->checkFileSize($fileData);
        if ($sizeError !== false) {
            return $sizeError;
        }

        // Check MIME types
        $mimeError = $this->checkMimeType($fileData);
        if ($mimeError !== false) {
            return $mimeError;
        }

        // Check max files (for single file, this is always 1)
        if (isset($this->rules['max_files']) && $this->rules['max_files'] > 1) {
            // This would be handled by validateMultipleFiles
        }

        return false; // Validation passed
    }

    private function validateMultipleFiles(): array|string|bool
    {
        $files = $this->normalizeMultipleFiles($this->inputValue);
        $maxFiles = $this->rules['max_files'] ?? 1;

        // Check total number of files
        if (count($files) > $maxFiles) {
            return $this->buildErrorMessage(
                'max_files',
                sprintf(
                    'Too many files for %s. Selected %d files, maximum allowed is %d',
                    $this->display,
                    count($files),
                    $maxFiles,
                ),
            );
        }

        $errors = [];
        foreach ($files as $index => $file) {
            $result = $this->validateSingleFileItem($file, $index);
            if ($result !== false) {
                $errors[] = $result;
            }
        }

        if (!empty($errors)) {
            // Return first error or all errors
            return $errors[0];
        }

        return false;
    }

    private function validateSingleFileItem(array $file, int $index): array|string|bool
    {
        // Check upload errors
        if (($this->rules['upload_error'] ?? false) === true) {
            $uploadError = $this->checkUploadErrors($file);
            if ($uploadError !== false) {
                return $uploadError;
            }
        }

        // Skip if no file for this index (partial uploads)
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return false;
        }

        // Check file size
        $sizeError = $this->checkFileSize($file);
        if ($sizeError !== false) {
            return $sizeError;
        }

        // Check MIME type
        $mimeError = $this->checkMimeType($file);
        if ($mimeError !== false) {
            return $mimeError;
        }

        return false;
    }

    private function checkUploadErrors(array $file): array|string|bool
    {
        $errorCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($errorCode !== UPLOAD_ERR_OK) {
            $fileName = $file['name'] ?? $this->display;
            $message = self::UPLOAD_ERROR_MESSAGES[$errorCode] ?? 'File upload failed';

            // Special handling for size errors
            if ($errorCode === UPLOAD_ERR_INI_SIZE) {
                $limit = ini_get('upload_max_filesize');
                $message = sprintf("'%s' exceeds server file size limit (%s)", $fileName, $limit);
            } elseif ($errorCode === UPLOAD_ERR_FORM_SIZE) {
                $message = sprintf("'%s' exceeds form upload limit", $fileName);
            } else {
                $message = sprintf($message, $fileName);
            }

            return $this->buildErrorMessage('upload_error', $message);
        }

        return false;
    }

    private function checkFileSize(array $file): array|string|bool
    {
        if (!isset($this->rules['file_size']) && !isset($this->rules['upload_limit'])) {
            return false;
        }

        $fileSize = $file['size'] ?? 0;
        $fileName = $file['name'] ?? $this->display;

        // Check application file_size limit
        if (isset($this->rules['file_size'])) {
            $maxSize = $this->parseSize($this->rules['file_size']);
            if ($fileSize > $maxSize) {
                $fileSizeFormatted = $this->formatBytes($fileSize);
                $maxSizeFormatted = $this->formatBytes($maxSize);
                return $this->buildErrorMessage(
                    'file_size',
                    sprintf(
                        '"%s" (%s) exceeds maximum file size (%s)',
                        $fileName,
                        $fileSizeFormatted,
                        $maxSizeFormatted,
                    ),
                );
            }
        }

        // Check PHP upload_limit (usually same as file_size but can be different)
        if (isset($this->rules['upload_limit'])) {
            $phpMaxSize = $this->parseSize($this->rules['upload_limit']);
            if ($fileSize > $phpMaxSize) {
                $fileSizeFormatted = $this->formatBytes($fileSize);
                $maxSizeFormatted = $this->formatBytes($phpMaxSize);
                return $this->buildErrorMessage(
                    'upload_limit',
                    sprintf(
                        '"%s" (%s) exceeds server file size limit (%s)',
                        $fileName,
                        $fileSizeFormatted,
                        $maxSizeFormatted,
                    ),
                );
            }
        }

        return false;
    }

    private function checkMimeType(array $file): array|string|bool
    {
        if (!isset($this->rules['mimes'])) {
            return false;
        }

        $mimeType = $file['type'] ?? '';
        $tmpName = $file['tmp_name'] ?? '';
        $fileName = $file['name'] ?? $this->display;

        // Get actual MIME type if possible
        if ($tmpName && file_exists($tmpName)) {
            $detectedMime = mime_content_type($tmpName);
            if ($detectedMime) {
                $mimeType = $detectedMime;
            }
        }

        $allowedMimes = $this->getAllowedMimes($this->rules['mimes']);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!in_array($mimeType, $allowedMimes, true)) {
            return $this->buildErrorMessage(
                'mimes',
                sprintf(
                    "File '%s' has invalid type '%s'. Allowed: %s",
                    $fileName,
                    $mimeType,
                    implode(', ', $allowedMimes),
                ),
            );
        }

        return false;
    }

    private function getAllowedMimes(string $mimeRule): array
    {
        return match($mimeRule) {
            'image' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/svg+xml',
                'image/bmp',
            ],
            'video' => [
                'video/mp4',
                'video/webm',
                'video/ogg',
                'video/quicktime',
                'video/x-msvideo',
                'video/x-flv',
                'video/3gpp',
            ],
            'pdf' => ['application/pdf'],
            'document' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
                'application/rtf',
            ],
            default => explode(',', $mimeRule),
        };
    }

    private function parseSize(string $size): int
    {
        $units = [
            'K' => 1024,
            'M' => 1024 * 1024,
            'G' => 1024 * 1024 * 1024,
        ];

        $size = trim($size);
        $unit = strtoupper(substr($size, -1));

        if (is_numeric($unit)) {
            return (int) $size;
        }

        $number = (float) substr($size, 0, -1);
        return (int) ($number * ($units[$unit] ?? 1));
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    private function normalizeFileData(mixed $input): array
    {
        if (is_array($input) && isset($input['tmp_name'])) {
            return $input;
        }

        // Handle $_FILES structure for single file
        return [
            'name' => $input['name'] ?? '',
            'type' => $input['type'] ?? '',
            'tmp_name' => $input['tmp_name'] ?? '',
            'error' => $input['error'] ?? UPLOAD_ERR_NO_FILE,
            'size' => $input['size'] ?? 0,
        ];
    }

    private function normalizeMultipleFiles(array $input): array
    {
        $files = [];

        if (isset($input['name']) && is_array($input['name'])) {
            $count = count($input['name']);
            for ($i = 0; $i < $count; $i++) {
                $files[] = [
                    'name' => $input['name'][$i],
                    'type' => $input['type'][$i] ?? '',
                    'tmp_name' => $input['tmp_name'][$i] ?? '',
                    'error' => $input['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $input['size'][$i] ?? 0,
                ];
            }
        }

        return $files;
    }

    private function buildErrorMessage(string $errorType, string $message): string
    {
        // Use message from errorParams if available, otherwise use provided message
        $finalMessage = $this->errorParams['message'] ?? $message;
        $classes = $this->errorParams['classes'] ?? ['text-danger', 'validation-error'];

        return $this->errorMessage($finalMessage, $classes);
    }
}