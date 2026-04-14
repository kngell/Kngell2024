<?php

declare(strict_types=1);

interface UploadMediapathsInterface
{
    public function getNewMediaPaths(): array;

    public function getExistingMediaPaths(): array;

    public function getRemovedPaths(): array;
}