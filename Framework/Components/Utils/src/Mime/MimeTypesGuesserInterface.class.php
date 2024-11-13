<?php

declare(strict_types=1);

interface MimeTypesGuesserInterface
{
    public function isSupported() : bool;

    public function guessMimeType(string $path) : string|null;
}
