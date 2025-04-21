<?php

declare(strict_types=1);

class MimeTypeGuesserFromConstants implements MimeTypesGuesserInterface
{
    public function isSupported(): bool
    {
        return class_exists(MimeTypeConstants::class);
    }

    public function guessMimeType(string $path): ?string
    {
        if (! $this->isSupported()) {
            return null;
        }
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (StringUtils::isBlanc($extension)) {
            return null;
        }

        return ArrayUtils::first(MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension]);
    }
}
