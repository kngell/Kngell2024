<?php

declare(strict_types=1);

trait MapperExtractorTraitOld
{
    private function extractFieldToMapping(array $field, array &$mapping): void
    {
        $name = $field['name'] ?? null;
        if (!$name) {
            return;
        }

        $sourcePath = $field['map'] ?? $name;

        if (str_starts_with($name, 'metadata[')) {
            $name = 'block_metadata' . substr($name, 8);
        }
        if (str_starts_with($sourcePath, 'metadata.')) {
            $sourcePath = 'block_metadata.' . substr($sourcePath, 9);
        }
        if (str_contains($sourcePath, '[')) {
            $sourcePath = $this->convertBracketsToDots($sourcePath);
        }

        $mapping[$sourcePath] = $name;
    }

    private function convertBracketsToDots(string $path): string
    {
        $cleanPath = str_replace(']', '', $path);
        return rtrim(str_replace('[', '.', $cleanPath), '.');
    }
}