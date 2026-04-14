<?php

// ObfuscatorFactory.php
declare(strict_types=1);

final class ObfuscatorFactory
{
    private array $instances = [];
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? ObfuscatorConfig::getConfig();
    }

    public function create(string $strategy): ObfuscatorInterface
    {
        // Return cached instance if available
        if (isset($this->instances[$strategy])) {
            return $this->instances[$strategy];
        }

        // Check if strategy is enabled
        if (!($this->config[$strategy]['enabled'] ?? true)) {
            throw new InvalidArgumentException("Obfuscation strategy '{$strategy}' is disabled");
        }

        $this->instances[$strategy] = match ($strategy) {
            'hashid' => $this->createHashidObfuscator(),
            'encrypt' => $this->createEncryptObfuscator(),
            default => throw new InvalidArgumentException("Unknown obfuscation strategy: {$strategy}")
        };

        return $this->instances[$strategy];
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    private function createHashidObfuscator(): HashidObfuscator
    {
        $config = $this->config['hashid'];

        return new HashidObfuscator(
            salt: $config['salt'] ?? 'default-salt',
            minLength: $config['min_length'] ?? 10,
            alphabet: $config['alphabet'] ?? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
        );
    }

    private function createEncryptObfuscator(): EncryptObfuscator
    {
        $config = $this->config['encrypt'];

        return new EncryptObfuscator(
            secretKey: $config['key'] ?? 'default-key',
            cipher: $config['cipher'] ?? 'aes-256-cbc',
            hmac: $config['hmac'] ?? true,
        );
    }
}