<?php

declare(strict_types=1);

final class ObfuscationUtils
{
    private function __construct()
    {
    }

    public static function isObfuscated(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }
        if (ObfuscatorConfig::hasPrefix($value)) {
            return true;
        }

        foreach (['hash', 'enc', 'obf'] as $fallback) {
            if (str_starts_with($value, $fallback) && strlen($value) > strlen($fallback)) {
                return true;
            }
        }

        return false;
    }

    public static function stripPrefix(string $value): string
    {
        foreach (ObfuscatorConfig::ALL_PREFIXES as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return substr($value, strlen($prefix));
            }
        }

        $colonlessPrefixes = ['hash', 'enc', 'obf'];
        foreach ($colonlessPrefixes as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return substr($value, strlen($prefix));
            }
        }

        return $value;
    }

    public static function getStrategyFromPrefix(string $value): string
    {
        if (str_starts_with($value, 'enc:') || str_starts_with($value, 'enc')) {
            return 'encrypt';
        }
        if (str_starts_with($value, 'obf:') || str_starts_with($value, 'obf')) {
            return 'obfuscation';
        }
        if (str_starts_with($value, 'hash:') || str_starts_with($value, 'hash') || str_starts_with($value, '#')) {
            return 'hashid';
        }

        return ObfuscatorConfig::getConfig()['default'] ?? 'hashid';
    }

    public static function extractRawId(mixed $value): mixed
    {
        if (!self::isObfuscated($value)) {
            return $value;
        }

        $stripped = self::stripPrefix((string) $value);
        if (is_numeric($stripped)) {
            return (int) $stripped;
        }

        return $stripped;
    }

    public static function addPrefix(string $rawId, ?string $strategy = null): string
    {
        return ObfuscatorConfig::addPrefix($rawId, $strategy);
    }
}