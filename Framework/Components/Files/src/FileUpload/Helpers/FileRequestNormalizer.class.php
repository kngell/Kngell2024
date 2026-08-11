<?php

declare(strict_types=1);

final class FileRequestNormalizer
{
    public static function normalize(array $files): array
    {
        $normalized = [];

        foreach ($files as $topKey => $fileData) {
            if (!is_array($fileData) || !isset($fileData['name'])) {
                continue;
            }

            if (is_array($fileData['name'])) {
                $flatPaths = [];
                self::getScalarPaths($fileData['name'], '', $flatPaths);

                foreach ($flatPaths as $subPath => $fileName) {
                    $fullFieldName = $topKey . $subPath;
                    $keysArray = self::convertSubPathToKeys($subPath);

                    $resultFile = [
                        'name' => $fileName,
                        'type' => self::getDeepValue($fileData['type'] ?? [], $keysArray),
                        'tmp_name' => self::getDeepValue($fileData['tmp_name'] ?? [], $keysArray),
                        'error' => self::getDeepValue($fileData['error'] ?? [], $keysArray),
                        'size' => self::getDeepValue($fileData['size'] ?? [], $keysArray),
                    ];
                    if (is_string($resultFile['tmp_name']) || is_numeric($resultFile['tmp_name'])) {
                        $normalized[$fullFieldName] = $resultFile;
                    }
                }
            } else {
                $normalized[$topKey] = $fileData;
            }
        }

        return $normalized;
    }

    private static function getScalarPaths(mixed $item, string $currentPath, array &$paths): void
    {
        if (is_array($item)) {
            foreach ($item as $key => $value) {
                self::getScalarPaths($value, $currentPath . '[' . $key . ']', $paths);
            }
        } else {
            $paths[$currentPath] = $item;
        }
    }

    private static function getDeepValue(array $array, array $keys): mixed
    {
        $current = $array;
        foreach ($keys as $key) {
            if (is_array($current) && isset($current[$key])) {
                $current = $current[$key];
            } else {
                return null;
            }
        }
        return $current;
    }

    private static function convertSubPathToKeys(string $subPath): array
    {
        $cleaned = trim($subPath, '[]');
        if ('' === $cleaned) {
            return [];
        }
        return explode('][', $cleaned);
    }
}