<?php

// ObfuscatorConfig.php
declare(strict_types=1);

final class ObfuscatorConfig
{
    // Global obfuscation prefix constants
    public const PREFIX = '#';
    public const ALTERNATIVE_PREFIXES = ['enc:', 'obf:', 'hash:'];
    public const ALL_PREFIXES = [self::PREFIX, ...self::ALTERNATIVE_PREFIXES];

    private static ?array $config = null;

    public static function getConfig(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        // Load from environment or config file
        $configFile = __DIR__ . '/../config/obfuscator.php';

        if (file_exists($configFile)) {
            self::$config = require $configFile;
        } else {
            self::$config = self::loadFromEnv();
        }

        return self::$config;
    }

    public static function getStrategyForModel(string $model): string
    {
        $config = self::getConfig();
        return $config['models'][$model] ?? $config['default'];
    }

    public static function isStrategyEnabled(string $strategy): bool
    {
        $config = self::getConfig();
        return $config[$strategy]['enabled'] ?? false;
    }

    public static function getStrategyConfig(string $strategy): array
    {
        $config = self::getConfig();
        return $config[$strategy] ?? [];
    }

    /**
     * Check if a string has an obfuscation prefix.
     */
    public static function hasPrefix(string $value): bool
    {
        foreach (self::ALL_PREFIXES as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Strip obfuscation prefix from a string.
     */
    public static function stripPrefix(string $value): string
    {
        foreach (self::ALL_PREFIXES as $prefix) {
            if (str_starts_with($value, $prefix)) {
                return substr($value, strlen($prefix));
            }
        }
        return $value;
    }

    public static function getStrategyFromPrefix(string $value): string
    {
        if (str_starts_with($value, 'enc:')) {
            return 'encrypt';
        }
        if (str_starts_with($value, 'obf:')) {
            return 'obfuscation';
        }
        if (str_starts_with($value, 'hash:')) {
            return 'hashid';
        }
        return self::getConfig()['default'] ?? 'hashid';
    }

    public static function addPrefix(string $rawId, ?string $strategy = null): string
    {
        if ($strategy === 'encrypt') {
            return 'enc:' . $rawId;
        }
        if ($strategy === 'obfuscation') {
            return 'obf:' . $rawId;
        }
        if ($strategy === 'hashid') {
            return 'hash:' . $rawId;
        }

        return self::PREFIX . $rawId;
    }

    private static function loadFromEnv(): array
    {
        $appKey = getenv('APP_KEY') ?: 'default-app-key-change-this';

        return [
            'default' => self::getEnv('OBFUSCATOR_DEFAULT', 'hashid'),

            'hashid' => [
                'enabled' => true,
                'salt' => self::getEnv('HASHID_SALT') ?: $appKey,
                'min_length' => (int) self::getEnv('HASHID_MIN_LENGTH', 10),
                'alphabet' => self::getEnv('HASHID_ALPHABET', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'),
            ],

            'encrypt' => [
                'enabled' => true,
                'key' => self::getEnv('ENCRYPT_KEY') ?: $appKey,
                'cipher' => self::getEnv('ENCRYPT_CIPHER', 'aes-256-cbc'),
                'hmac' => (bool) self::getEnv('ENCRYPT_HMAC', true),
            ],

            'models' => [
                'user' => 'hashid',
                'product' => 'hashid',
                'order' => 'encrypt',
                'payment' => 'encrypt',
            ],
        ];
    }

    private static function getEnv(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}