<?php

declare(strict_types=1);

class FileUpload extends FileInformation
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
        if (!empty($this->mimeType) && $this->isProvidedMimeValid()) {
            return $this->mimeType;
        }
        return parent::getMimeType();
    }

    public function getMimeTypeSafe(): string
    {
        if (!empty($this->mimeType)) {
            return $this->mimeType;
        }

        $extension = $this->getOriginalExtension();
        if (!empty($extension)) {
            $extension = strtolower($extension);
            if (isset(MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension])) {
                $mimeTypes = MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension];
                return $mimeTypes[0] ?? 'application/octet-stream';
            }
        }

        return 'application/octet-stream';
    }

    public function getClientMediaType(): string
    {
        return $this->mimeType;
    }

    public function isSafeForUpload(): bool
    {
        if (!parent::isSafeForUpload()) {
            return false;
        }
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

        $safeName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $filename) ?? 'file';

        return $extension ? $safeName . '.' . $extension : $safeName;
    }

    public function guessExtension(): ?string
    {
        $extension = MimeTypeGuessDelegator::getInstance()->guessExtensionByMimeType($this->mimeType);
        return ArrayUtils::first($extension);
    }

    public function getUploadErrorDescription(): string
    {
        return $this->uploadError->getErrorMessage($this->originalName);
    }

    private function isProvidedMimeValid(): bool
    {
        $detectedMime = parent::getMimeType();
        return $this->mimeType === $detectedMime;
    }
}