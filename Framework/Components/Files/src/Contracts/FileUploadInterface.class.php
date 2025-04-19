<?php

declare(strict_types=1);

interface FileUploadInterface
{
    public function proceed(bool $uploadRequired = false) : void;

    public function getMediaPaths(): string|null;

    public function getErrors(): array;
}