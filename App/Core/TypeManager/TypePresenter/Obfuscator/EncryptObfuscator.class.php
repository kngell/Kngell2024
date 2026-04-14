<?php

// EncryptObfuscator.php
declare(strict_types=1);

final class EncryptObfuscator implements ObfuscatorInterface
{
    use UrlSafeEncodingTrait;
    use RandomGeneratorTrait;

    private string $cipher;
    private string $key;
    private string $hmacKey;
    private bool $useHmac;

    public function __construct(
        string $secretKey,
        string $cipher = 'aes-256-cbc',
        bool $hmac = true,
    ) {
        $this->cipher = $cipher;
        $this->useHmac = $hmac;
        $this->key = substr(hash('sha256', $secretKey, true), 0, 32);

        if ($this->useHmac) {
            $this->hmacKey = substr(hash('sha256', $secretKey . '_hmac', true), 0, 32);
        }
    }

    public function obfuscate(int $value): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt((string) $value, $this->cipher, $this->key, 0, $iv);
        $data = $iv . $encrypted;

        if ($this->useHmac) {
            $hmac = hash_hmac('sha256', $data, $this->hmacKey, true);
            $data = $hmac . $data;
        }

        return $this->urlSafeEncode($data);
    }

    public function deobfuscate(string $value): ?int
    {
        try {
            $decoded = $this->urlSafeDecode($value);

            if ($this->useHmac) {
                $hmacLength = 32; // sha256 = 32 bytes
                $hmac = substr($decoded, 0, $hmacLength);
                $data = substr($decoded, $hmacLength);

                $expectedHmac = hash_hmac('sha256', $data, $this->hmacKey, true);
                if (!hash_equals($expectedHmac, $hmac)) {
                    return null;
                }
            } else {
                $data = $decoded;
            }

            $ivLength = openssl_cipher_iv_length($this->cipher);
            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);

            $result = openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
            return $result !== false ? (int) $result : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function generate(): string
    {
        return $this->obfuscate(random_int(100000, 999999999));
    }
}