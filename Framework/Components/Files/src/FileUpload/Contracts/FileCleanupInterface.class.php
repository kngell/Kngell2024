<?php

declare(strict_types=1);

interface FileCleanupInterface
{
    public function cleanupProcessedFiles(array $filePaths): int;

    public function cleanupOrphanedFiles(array $activePaths = []): int;

    public function cleanupAgedFiles(int $maxAgeSeconds = 3600): int;

    public function cleanup(): void;
}