<?php

declare(strict_types=1);

class UploadErrorValidator extends AbstractValidator
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

        $errors = [];

        // Handle single FileUpload object
        if ($this->inputValue instanceof FileUpload) {
            $error = $this->validateFileUpload($this->inputValue);
            if ($error !== null) {
                $errors[] = $error;
            }
        }
        // Handle array of FileUpload objects (like img_gallery)
        elseif (is_array($this->inputValue)) {
            foreach ($this->inputValue as $file) {
                if ($file instanceof FileUpload) {
                    $error = $this->validateFileUpload($file);
                    if ($error !== null) {
                        $errors[] = $error;
                    }
                }
            }
        }

        if (!empty($errors)) {
            return $this->errorMessage(
                implode('; ', $errors),
                $this->errorParams['classes'],
            );
        }

        return false;
    }

    protected function isEmpty(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === []) {
            return true;
        }

        // For FileUpload objects
        if ($value instanceof FileUpload) {
            return $value->getError() === ErrorFile::UPLOAD_ERR_NO_FILE;
        }

        // For arrays of FileUpload objects
        if (is_array($value)) {
            foreach ($value as $file) {
                if ($file instanceof FileUpload && $file->getError() !== ErrorFile::UPLOAD_ERR_NO_FILE) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    private function validateFileUpload(FileUpload $file): ?string
    {
        $error = $file->getError();

        // Skip if no error or no file
        if ($error->isSuccess() || $error === ErrorFile::UPLOAD_ERR_NO_FILE) {
            return null;
        }

        // Get user-friendly error message
        return $error->getUserFriendlyMessage($file->getOriginalName());
    }
}