<?php

declare(strict_types=1);

final class ByteHelper
{
    public static function format(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = (int) floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $value = $bytes / pow(1024, $pow);

        return round($value, $precision) . ' ' . $units[$pow];
    }
}