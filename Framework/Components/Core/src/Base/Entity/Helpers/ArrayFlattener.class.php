<?php

declare(strict_types=1);
final readonly class ArrayFlattener
{
    public function flatten(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = ($prefix === '') ? (string) $key : $prefix . '[' . $key . ']';

            if (is_array($value) && !empty($value) && !str_ends_with((string) $key, '[]')) {
                $result = array_merge($result, $this->flatten($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    public function flattenWithSeparator(array $array, string $separator = '.', string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            // Logic: No separator if prefix is empty
            $newKey = ($prefix === '') ? (string) $key : $prefix . $separator . $key;

            if (is_array($value) && !empty($value)) {
                $result = array_merge($result, $this->flattenWithSeparator($value, $separator, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}