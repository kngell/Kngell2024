<?php

declare(strict_types=1);

class UploadLimitValidator extends AbstractValidator
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
        $phpUploadLimit = $this->parseSize((string) $this->ruleValue);
        $phpActualLimit = $this->getPhpUploadMaxFilesize();

        if ($phpUploadLimit !== $phpActualLimit) {
            throw new FileUploadConfigurationException(sprintf(
                'Configuration mismatch: PHP upload_max_filesize is %s, but rule expects %s',
                $this->formatBytes($phpActualLimit),
                $this->formatBytes($phpUploadLimit),
            ));
        }
        if (!$this->isEmpty($this->inputValue)) {
            $files = is_array($this->inputValue) ? $this->inputValue : [$this->inputValue];

            foreach ($files as $file) {
                if ($this->isValidFile($file)) {
                    $fileSize = $this->getFileSize($file);

                    if ($fileSize > $phpActualLimit) {
                        // This file will be rejected by PHP before our app even sees it
                        return $this->errorMessage(
                            sprintf(
                                $this->errorParams['message'] ?? '"%s" (%s) exceeds PHP server upload limit (%s). ' .
                                'The server will reject this file before it reaches the application.',
                                $this->getFileName($file),
                                $this->formatBytes($fileSize),
                                $this->formatBytes($phpActualLimit),
                            ),
                            $this->errorParams['classes'],
                        );
                    }
                }
            }
        }

        return false;
    }

    private function getPhpUploadMaxFilesize(): int
    {
        return $this->parseSize(ini_get('upload_max_filesize'));
    }

    private function getFileName(mixed $file): string
    {
        if ($file instanceof FileUpload) {
            return $file->getOriginalName();
        }

        if (is_array($file) && isset($file['name'])) {
            return $file['name'];
        }

        return 'Unknown file';
    }

    private function isValidFile(mixed $file): bool
    {
        if ($file instanceof FileUpload) {
            return $file->isValid() && !$file->hasError();
        }

        return is_array($file) && isset($file['size']) && $file['size'] > 0;
    }

    private function getFileSize(mixed $file): int
    {
        if ($file instanceof FileUpload) {
            return $file->getSize();
        }

        return $file['size'] ?? 0;
    }

    private function parseSize(string $size): int
    {
        $units = ['B' => 1, 'K' => 1024, 'M' => 1048576, 'G' => 1073741824];
        $pattern = '/^(\d+(?:\.\d+)?)\s*([BKMGT]?)?$/i';

        if (preg_match($pattern, strtoupper($size), $matches)) {
            $value = (float) $matches[1];
            $unit = $matches[2] ?? 'B';
            return (int) ($value * ($units[$unit] ?? 1));
        }

        return 0;
    }

    private function formatBytes(int $bytes, int $decimals = 2): string
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }

        $k = 1024;
        $dm = max($decimals, 0);
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes) / log($k));

        return number_format($bytes / pow($k, $i), $dm) . ' ' . $sizes[$i];
    }
}