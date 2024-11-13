<?php

declare(strict_types=1);

class FileInfoMimeTypesGuesser implements MimeTypesGuesserInterface
{
    public function isSupported(): bool
    {
        return function_exists('finfo_open');
    }

    public function guessMimeType(string $path): ?string
    {
        if (! $this->isSupported()) {
            return null;
        }
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException("The '{$path}' does not exist or is not readable");
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($path);
        if (! $mimeType) {
            return null;
        }
        return $mimeType;
    }
}
