<?php

declare(strict_types=1);

class MaxFilesValidator extends AbstractValidator
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

        $fileCount = $this->countValidFiles();
        $maxFiles = (int) $this->ruleValue;

        if ($fileCount > $maxFiles) {
            $errorMsg = sprintf(
                $this->errorParams['message'],
                $this->display,
                $fileCount,
                $maxFiles,
            );

            return $this->errorMessage($errorMsg, $this->errorParams['classes']);
        }

        return false;
    }

    protected function isEmpty(mixed $value): bool
    {
        if ($value instanceof FileUpload) {
            return $value->hasError() || $value->getUploadError() === UPLOAD_ERR_NO_FILE;
        }

        if (is_array($value)) {
            // Check if it's an empty PHP files array
            if (isset($value['name'])) {
                if (is_array($value['name'])) {
                    foreach ($value['name'] as $index => $name) {
                        if (!empty($name)) {
                            return false;
                        }
                    }

                    return true;
                }
                return empty($value['name']);
            }
            return empty($value);
        }

        return $value === null || $value === '' || $value === '[]';
    }

    private function countValidFiles(): int
    {
        $files = $this->normalizeFiles($this->inputValue);

        $count = 0;
        foreach ($files as $index => $file) {
            $isValid = $this->isValidFile($file);
            if ($isValid) {
                $count++;
            } else {
            }
        }

        return $count;
    }

    private function normalizeFiles(mixed $input): array
    {
        // Handle raw PHP $_FILES array structure for multiple files
        if (is_array($input) && isset($input['name']) && is_array($input['name'])) {
            return $this->normalizePhpFilesArray($input);
        }

        // Handle pre-normalized array (array of FileUpload objects or file arrays)
        if (is_array($input) && !empty($input)) {
            return $input;
        }

        // Handle single FileUpload object
        if ($input instanceof FileUpload) {
            return [$input];
        }

        // Handle single file array
        if (is_array($input) && isset($input['name']) && !is_array($input['name'])) {
            return [$input];
        }

        return [];
    }

    private function normalizePhpFilesArray(array $phpFiles): array
    {
        $normalized = [];

        if (isset($phpFiles['name']) && is_array($phpFiles['name'])) {
            $fileCount = count($phpFiles['name']);

            for ($i = 0; $i < $fileCount; $i++) {
                $name = $phpFiles['name'][$i] ?? '';
                $error = $phpFiles['error'][$i] ?? UPLOAD_ERR_NO_FILE;

                // Skip empty file entries or no-file errors
                if (empty($name) || $error === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $normalized[] = [
                    'name' => $name,
                    'full_path' => $phpFiles['full_path'][$i] ?? $name,
                    'type' => $phpFiles['type'][$i] ?? '',
                    'tmp_name' => $phpFiles['tmp_name'][$i] ?? '',
                    'error' => $error,
                    'size' => $phpFiles['size'][$i] ?? 0,
                ];
            }
        } else {
        }

        return $normalized;
    }

    private function isValidFile(mixed $file): bool
    {
        if ($file instanceof FileUpload) {
            return $file->isValid() && !$file->hasError();
        }

        // For file arrays, check if it's a valid uploaded file
        if (is_array($file)) {
            $hasRequiredFields = isset($file['name'], $file['tmp_name'], $file['size'], $file['error']);
            $hasNoError = $file['error'] === UPLOAD_ERR_OK;
            $hasSize = $file['size'] > 0;
            $isUploaded = !empty($file['tmp_name']) && is_uploaded_file($file['tmp_name']);

            return $hasRequiredFields && $hasNoError && $hasSize && $isUploaded;
        }

        return false;
    }
}