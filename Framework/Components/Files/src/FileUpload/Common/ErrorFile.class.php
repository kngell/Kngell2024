<?php

declare(strict_types=1);

enum ErrorFile: int
{
    private const UPLOAD_ERROR_MESSAGES = [
        // PHP built-in errors
        UPLOAD_ERR_OK => 'File uploaded successfully',
        UPLOAD_ERR_INI_SIZE => "File '%s' exceeds the upload_max_filesize directive (limit: %s)",
        UPLOAD_ERR_FORM_SIZE => "File '%s' exceeds the MAX_FILE_SIZE directive in the form",
        UPLOAD_ERR_PARTIAL => "File '%s' was only partially uploaded",
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary directory',
        UPLOAD_ERR_CANT_WRITE => "Failed to write file '%s' to disk",
        UPLOAD_ERR_EXTENSION => 'File upload stopped by PHP extension',

        // System operation errors
        1003 => "Could not move file '%s' to destination",
        1004 => "Could not create file '%s'",
        1007 => "Directory '%s' is not writable",
    ];

    public function getErrorMessage(string ...$params): string
    {
        $message = self::UPLOAD_ERROR_MESSAGES[$this->value]
            ?? 'An unknown file upload error occurred';

        return empty($params) ? $message : sprintf($message, ...$params);
    }

    public function isSuccess(): bool
    {
        return $this === self::UPLOAD_ERR_OK;
    }

    public function isPhpError(): bool
    {
        return $this->value >= 0 && $this->value <= 8;
    }

    public function isSystemError(): bool
    {
        return $this->value >= 1000;
    }

    public function getUserFriendlyMessage(string $fileName = ''): string
    {
        return match ($this) {
            // Success - should not be shown to users normally
            self::UPLOAD_ERR_OK => 'File uploaded successfully',

            // Size-related errors
            self::UPLOAD_ERR_INI_SIZE,
            self::UPLOAD_ERR_FORM_SIZE => "File '{$fileName}' is too large",

            // Upload process errors
            self::UPLOAD_ERR_PARTIAL => "File '{$fileName}' was only partially uploaded",
            self::UPLOAD_ERR_NO_FILE => 'Please select a file to upload',
            self::UPLOAD_ERR_CANT_WRITE => "Could not save file '{$fileName}'",
            self::UPLOAD_ERR_EXTENSION => 'File upload was blocked',

            // System errors
            self::UPLOAD_ERR_NO_TMP_DIR,
            self::DIRECTORY_NOT_WRITABLE => 'System error: Unable to process upload',

            // File operation errors
            self::MOVE_OPERATION_FAILED,
            self::CREATE_OPERATION_FAILED => "Unable to save file '{$fileName}'",

            // Fallback
            default => "Error uploading file '{$fileName}'"
        };
    }

    public static function fromUploadError(int $errorCode): self
    {
        // Handle custom system errors first
        if ($errorCode >= 1000) {
            return match ($errorCode) {
                1003 => self::MOVE_OPERATION_FAILED,
                1004 => self::CREATE_OPERATION_FAILED,
                1007 => self::DIRECTORY_NOT_WRITABLE,
                default => throw new InvalidArgumentException("Invalid error code: {$errorCode}")
            };
        }

        // Handle PHP upload errors
        return match ($errorCode) {
            UPLOAD_ERR_OK => self::UPLOAD_ERR_OK,
            UPLOAD_ERR_INI_SIZE => self::UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => self::UPLOAD_ERR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL => self::UPLOAD_ERR_PARTIAL,
            UPLOAD_ERR_NO_FILE => self::UPLOAD_ERR_NO_FILE,
            UPLOAD_ERR_NO_TMP_DIR => self::UPLOAD_ERR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE => self::UPLOAD_ERR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION => self::UPLOAD_ERR_EXTENSION,
            default => throw new InvalidArgumentException("Invalid upload error code: {$errorCode}")
        };
    }

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getPhpErrors(): array
    {
        return array_filter(self::cases(), fn ($case) => $case->isPhpError());
    }

    public static function getSystemErrors(): array
    {
        return array_filter(self::cases(), fn ($case) => $case->isSystemError());
    }
    // PHP built-in upload errors (0-8)
    case UPLOAD_ERR_OK = 0;           // 0
    case UPLOAD_ERR_INI_SIZE = 1;     // 1
    case UPLOAD_ERR_FORM_SIZE = 2;    // 2
    case UPLOAD_ERR_PARTIAL = 3;      // 3
    case UPLOAD_ERR_NO_FILE = 4;      // 4
    case UPLOAD_ERR_NO_TMP_DIR = 6;   // 6
    case UPLOAD_ERR_CANT_WRITE = 7;   // 7
    case UPLOAD_ERR_EXTENSION = 8;    // 8

    // Custom system operation errors (1000+ range to avoid conflicts)
    case MOVE_OPERATION_FAILED = 1003;
    case CREATE_OPERATION_FAILED = 1004;
    case DIRECTORY_NOT_WRITABLE = 1007;
}