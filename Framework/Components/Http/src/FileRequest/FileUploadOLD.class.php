<?php

declare(strict_types=1);

class FileUploadOLD extends FileInformation
{
    public function __construct(
        string $path,
        private readonly string $originalName,
        private readonly string $mimeType,
        private ErrorFile $uploadError,
        private int $size,
    ) {
        parent::__construct($path);
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getMimeType(): string
    {
        // Use provided MIME type if available and valid
        if (!empty($this->mimeType) && $this->isProvidedMimeValid()) {
            return $this->mimeType;
        }

        // Fallback to parent detection
        return parent::getMimeType();
    }

    /**
     * Security validation for uploaded files.
     */
    public function isSafeForUpload(): bool
    {
        if (!parent::isSafeForUpload()) {
            return false;
        }

        // Additional upload-specific checks
        $dangerousExtensions = ['php', 'phtml', 'phar', 'htaccess'];
        $extension = $this->getOriginalExtension();

        return !in_array(strtolower($extension), $dangerousExtensions);
    }

    public function getUploadError(): int
    {
        return $this->uploadError->value;
    }

    public function getError(): ErrorFile
    {
        return $this->uploadError;
    }

    public function setError(ErrorFile $error): void
    {
        $this->uploadError = $error;
    }

    public function isValid(): bool
    {
        return $this->uploadError === ErrorFile::UPLOAD_ERR_OK
            && is_uploaded_file($this->getPathname());
    }

    public function hasError(): bool
    {
        return $this->uploadError !== ErrorFile::UPLOAD_ERR_OK;
    }

    public function getOriginalExtension(): string
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION) ?? '';
    }

    public function getSafeFilename(): string
    {
        $filename = pathinfo($this->originalName, PATHINFO_FILENAME) ?? 'file';
        $extension = $this->getOriginalExtension();

        // Sanitize filename - only allow alphanumeric, dots, hyphens, underscores
        $safeName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $filename) ?? 'file';

        return $extension ? $safeName . '.' . $extension : $safeName;
    }

    public function guessExtension(): ?string
    {
        // Use the delegator for extension guessing
        $extension = MimeTypeGuessDelegator::getInstance()->guessExtensionByMimeType($this->mimeType);
        return ArrayUtils::first($extension);
    }

    public function getUploadErrorDescription(): string
    {
        $params = [$this->originalName];

        // Add additional parameters for specific error types
        switch ($this->uploadError) {
            case ErrorFile::UPLOAD_ERR_INI_SIZE:
                $params[] = ini_get('upload_max_filesize');
                break;
            case ErrorFile::FILE_SIZE_EXCEEDED:
                // You might want to pass the max allowed size here
                $params[] = '5MB'; // This should come from configuration
                break;
            case ErrorFile::FILE_TYPE_NOT_ALLOWED:
                // You might want to pass allowed types here
                $params[] = 'jpg, png, pdf'; // This should come from configuration
                break;
        }

        return $this->uploadError->getErrorMessage(...$params);
    }

    public function isSizeWithinLimit(int $maxSizeInBytes): bool
    {
        return $this->getSize() <= $maxSizeInBytes;
    }

    public function isExtensionAllowed(array $allowedExtensions): bool
    {
        $extension = $this->getOriginalExtension();
        return in_array(strtolower($extension), array_map('strtolower', $allowedExtensions));
    }

    public function isMimeTypeAllowed(array $allowedMimeTypes): bool
    {
        return in_array($this->getMimeType(), $allowedMimeTypes);
    }

    /**
     * Quick validation helper.
     */
    public function validate(array $options = []): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check size limit if provided
        if (isset($options['maxSize']) && !$this->isSizeWithinLimit($options['maxSize'])) {
            $this->setError(ErrorFile::FILE_SIZE_EXCEEDED);
            return false;
        }

        // Check extensions if provided
        if (isset($options['allowedExtensions']) && !$this->isExtensionAllowed($options['allowedExtensions'])) {
            $this->setError(ErrorFile::FILE_TYPE_NOT_ALLOWED);
            return false;
        }

        // Check MIME types if provided
        if (isset($options['allowedMimeTypes']) && !$this->isMimeTypeAllowed($options['allowedMimeTypes'])) {
            $this->setError(ErrorFile::MIME_TYPE_NOT_ALLOWED);
            return false;
        }

        return true;
    }

    /**
     * Validate that provided MIME type matches actual file content.
     */
    private function isProvidedMimeValid(): bool
    {
        $detectedMime = parent::getMimeType();
        return $this->mimeType === $detectedMime;
    }
}