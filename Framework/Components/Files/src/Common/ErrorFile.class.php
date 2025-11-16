<?php

declare(strict_types=1);

enum ErrorFile: int
{
    private const UPLOAD_ERROR_MESSAGES = [
        // PHP built-in errors only
        UPLOAD_ERR_OK => 'File uploaded successfully',
        UPLOAD_ERR_INI_SIZE => "File '%s' exceeds the upload_max_filesize directive (limit: %s)",
        UPLOAD_ERR_FORM_SIZE => "File '%s' exceeds the MAX_FILE_SIZE directive in the form",
        UPLOAD_ERR_PARTIAL => "File '%s' was only partially uploaded",
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary directory',
        UPLOAD_ERR_CANT_WRITE => "Failed to write file '%s' to disk",
        UPLOAD_ERR_EXTENSION => 'File upload stopped by PHP extension',

        // System operation errors only
        1003 => "Could not move file '%s' to destination",
        1004 => "Could not create file '%s'",
        1007 => "Directory '%s' is not writable",
    ];

    public function getErrorMessage(string ...$params): string
    {
        $message = self::UPLOAD_ERROR_MESSAGES[$this->value]
            ?? 'An unknown file upload error occurred';

        if (!empty($params)) {
            return sprintf($message, ...$params);
        }

        return $message;
    }

    public function isSuccess(): bool
    {
        return $this === self::UPLOAD_ERR_OK;
    }

    public function getUserFriendlyMessage(string $fileName = ''): string
    {
        return match ($this) {
            // PHP upload size errors
            self::UPLOAD_ERR_INI_SIZE,
            self::UPLOAD_ERR_FORM_SIZE => "File '{$fileName}' is too large",

            // PHP upload errors
            self::UPLOAD_ERR_PARTIAL => "File '{$fileName}' was only partially uploaded",
            self::UPLOAD_ERR_NO_FILE => 'Please select a file to upload',

            // System operation errors
            self::MOVE_OPERATION_FAILED,
            self::CREATE_OPERATION_FAILED => "Unable to save file '{$fileName}'",
            self::UPLOAD_ERR_NO_TMP_DIR,
            self::DIRECTORY_NOT_WRITABLE => 'System error: Unable to save files',

            default => "Error uploading file '{$fileName}'"
        };
    }

    public static function fromUploadError(int $errorCode): self
    {
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
    // KEEP ONLY PHP built-in errors - system level
    case UPLOAD_ERR_OK = UPLOAD_ERR_OK;
    case UPLOAD_ERR_INI_SIZE = UPLOAD_ERR_INI_SIZE;
    case UPLOAD_ERR_FORM_SIZE = UPLOAD_ERR_FORM_SIZE;
    case UPLOAD_ERR_PARTIAL = UPLOAD_ERR_PARTIAL;
    case UPLOAD_ERR_NO_FILE = UPLOAD_ERR_NO_FILE;
    case UPLOAD_ERR_NO_TMP_DIR = UPLOAD_ERR_NO_TMP_DIR;
    case UPLOAD_ERR_CANT_WRITE = UPLOAD_ERR_CANT_WRITE;
    case UPLOAD_ERR_EXTENSION = UPLOAD_ERR_EXTENSION;

    // KEEP ONLY system operation errors
    case MOVE_OPERATION_FAILED = 1003;
    case CREATE_OPERATION_FAILED = 1004;
    case DIRECTORY_NOT_WRITABLE = 1007;
}