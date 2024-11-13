<?php

declare(strict_types=1);

final readonly class FileUtils
{
    private function __construct()
    {
    }

    public static function getMaxFileSizeFromPhpSettings() : int
    {
        $postMaxSize = self::parseIniFileSize(ini_get('post_max_size'));
        $uploadFileSize = self::parseIniFileSize(ini_get('upload_max_filesize'));
        return min($postMaxSize ?? PHP_INT_MAX, $uploadFileSize ?? PHP_INT_MAX);
    }

    private static function parseIniFileSize(string|false $size) : int|null
    {
        if ($size === false || StringUtils::isBlanc($size)) {
            return null;
        }

        $size = strtolower($size);
        $max = ltrim($size, '+');
        if (str_starts_with($max, '0x')) {
            $intSize = intval($max, 16);
        } elseif (str_starts_with($max, '0')) {
            $intSize = intval($max, 8);
        } else {
            $intSize = (int) $max;
        }
        $unit = strtoupper(substr($size, -1));

        switch ($unit) {
            case 'T':
                $intSize *= 1024;
                // no break
            case 'G':
                $intSize *= 1024;
                // no break
            case 'M':
                $intSize *= 1024;
                // no break
            case 'K':
                $intSize *= 1024;
        }
        return $intSize;
    }
}
