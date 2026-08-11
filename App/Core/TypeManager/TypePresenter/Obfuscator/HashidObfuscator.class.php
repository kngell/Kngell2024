<?php

declare(strict_types=1);

use Hashids\Hashids;

final class HashidObfuscator implements ObfuscatorInterface
{
    use UrlSafeEncodingTrait;

    private Hashids $hashids;
    private array $config;

    public function __construct(
        string $salt,
        int $minLength = 10,
        string $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
    ) {
        $this->hashids = new Hashids($salt, $minLength, $alphabet);
        $this->config = compact('salt', 'minLength', 'alphabet');
    }

    public function obfuscate(int $value): string
    {
        return $this->hashids->encode($value);
    }

    public function deobfuscate(string $value): ?int
    {
        $decoded = $this->hashids->decode($value);
        return $decoded[0] ?? null;
    }

    public function generate(): string
    {
        return $this->obfuscate(random_int(100000, 999999));
    }
}