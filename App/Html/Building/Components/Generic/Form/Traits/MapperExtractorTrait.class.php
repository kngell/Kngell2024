<?php

declare(strict_types=1);

trait MapperExtractorTrait
{
    private function extractFieldToMapping(array $field, array &$mapping): void
    {
        $name = $field['name'] ?? null; // e.g. "metadata[image][url][]"
        if (!$name) {
            return;
        }

        $sourcePath = $field['map'] ?? $name;
        $isMultiple = $field['multiple'] ?? false;

        if (str_starts_with($name, 'metadata[')) {
            $name = 'block_metadata' . substr($name, 8);
        }
        if (str_starts_with($sourcePath, 'metadata.')) {
            $sourcePath = 'block_metadata.' . substr($sourcePath, 9);
        }

        // Fix: Only convert brackets to dots for the internal entity source path, NOT the target $name
        if (str_contains($sourcePath, '[')) {
            $sourcePath = $this->convertBracketsToDots($sourcePath, $isMultiple);
        }
        if (str_contains($name, '[') && $isMultiple) {
            $name = $this->convertBracketsToDots($name, $isMultiple);
        }

        $mapping[$sourcePath] = $name;
    }

    private function convertBracketsToDots(string $path, bool $isMultiple = false): string
    {
        $converted = str_replace(['][', '[', ']'], ['.', '.', ''], $path);

        $converted = trim($converted, '.');
        $converted = str_replace('..', '.', $converted);

        if ($isMultiple) {
            $converted .= '.*.';
        }

        return $converted;
    }
}