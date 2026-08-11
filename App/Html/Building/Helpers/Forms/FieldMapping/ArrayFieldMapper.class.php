<?php

declare(strict_types=1);

final class ArrayFieldMapper
{
    public function toFormArray(array $source, FormFieldMappingPayloadInterface $mapping): array
    {
        $flatSource = $this->flattenForForm($source, $mapping->getFieldMapping());
        $result = [];

        foreach ($mapping->getFieldMapping() as $sourcePattern => $targetPattern) {
            if (str_contains($sourcePattern, '*')) {
                $this->mapWildcardPattern($sourcePattern, $targetPattern, $flatSource, $result);
            } else {
                $normalizedSourcePattern = str_replace('.', '[', $sourcePattern);
                if (str_contains($normalizedSourcePattern, '[')) {
                    $normalizedSourcePattern .= ']';
                }

                if (array_key_exists($normalizedSourcePattern, $flatSource)) {
                    $result[$targetPattern] = $flatSource[$normalizedSourcePattern];
                    unset($flatSource[$normalizedSourcePattern]);
                } elseif (array_key_exists($sourcePattern, $flatSource)) {
                    $result[$targetPattern] = $flatSource[$sourcePattern];
                    unset($flatSource[$sourcePattern]);
                }
            }
        }
        return array_merge($flatSource, $result);
    }

    private function flattenForForm(array $array, array $mappings, string $prefix = ''): array
    {
        if (ArrayUtils::isDeepEmpty($array)) {
            return [];
        }

        $result = [];

        foreach ($array as $key => $value) {
            $currentKey = $prefix ? "{$prefix}[{$key}]" : (string) $key;
            $currentKey = $this->removeTrailingNumericIndices($currentKey);

            if (is_array($value)) {
                if ($this->shouldKeepAsNativeArray($currentKey, $mappings)) {
                    $targetKey = $this->getTargetKeyForPath($currentKey, $mappings);
                    $result[$targetKey] = $value;
                } else {
                    $result = array_merge($result, $this->flattenForForm($value, $mappings, $currentKey));
                }
            } else {
                $result[$currentKey] = $value;
            }
        }

        return $result;
    }

    private function removeTrailingNumericIndices(string $key): string
    {
        return preg_replace('/\[\d+\]$/', '', $key);
    }

    private function shouldKeepAsNativeArray(string $currentPath, array $mappings): bool
    {
        foreach ($mappings as $sourcePattern => $targetPattern) {
            if (str_ends_with($targetPattern, '[]')) {
                $cleanTarget = str_replace('[]', '', $targetPattern);
                if ($currentPath === $sourcePattern || $currentPath === $cleanTarget) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getTargetKeyForPath(string $currentPath, array $mappings): string
    {
        foreach ($mappings as $sourcePattern => $targetPattern) {
            if ($currentPath === $sourcePattern && str_ends_with($targetPattern, '[]')) {
                return $targetPattern;
            }
        }
        return $currentPath . '[]';
    }

    private function mapWildcardPattern(string $sourcePattern, string $targetPattern, array &$flatSource, array &$result): void
    {
        $regexPattern = $this->buildRegexFromPattern($sourcePattern);

        foreach ($flatSource as $flatKey => $value) {
            if (preg_match($regexPattern, $flatKey, $matches)) {
                array_shift($matches);

                $replacedTarget = $targetPattern;
                foreach ($matches as $index) {
                    $replacedTarget = preg_replace('/\*/', (string) $index, $replacedTarget, 1);
                }

                $result[$replacedTarget] = $value;
                unset($flatSource[$flatKey]);
            }
        }
    }

    private function buildRegexFromPattern(string $pattern): string
    {
        $parts = explode('.*.', $pattern);
        $escapedParts = array_map(function ($part) {
            return str_replace(['.', '[', ']'], ['\.', '\[', '\]'], $part);
        }, $parts);

        return '/^' . implode('\[(\d+)\]', $escapedParts) . '$/';
    }
}