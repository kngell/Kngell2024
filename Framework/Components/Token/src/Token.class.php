<?php

// Token.php
declare(strict_types=1);

class Token implements TokenInterface
{
    use UrlSafeEncodingTrait;
    use RandomGeneratorTrait;

    private const string SEPARATOR = 'csrf_token_separator';
    private const int DEFAULT_CSRF_LENGTH = 32;
    private const int DEFAULT_AUTH_EXPIRY = 3600; // 1 hour

    private ?string $token;

    public function __construct(string|null $token = null)
    {
        $this->token = $token;
        // if ($token === null) {
        //     $this->token = $this->generate(self::DEFAULT_CSRF_LENGTH);
        // } else {
        //     $this->token = $token;
        // }
    }

    public function getCsrfHash(
        int $length = self::DEFAULT_CSRF_LENGTH,
        string $frm = '',
        string $alphabet = '',
    ): string {
        $time = time();
        $separator = !empty($frm) ? $frm : self::SEPARATOR;

        $this->token = $this->generate($length, $alphabet);
        // Re-generate token if length doesn't match
        // if (is_null($this->token) || strlen($this->token) !== $length) {
        //     $this->token = $this->generate($length, $alphabet);
        // }

        $hash = hash_hmac('sha256', session_id() . $this->token . $time . $frm, CSRF_TOKEN_SECRET, true);
        return $this->urlSafeEncode($hash . $separator . $this->token . $separator . $time);
    }

    public function generate(int $length = self::DEFAULT_CSRF_LENGTH, string $alphabet = ''): string
    {
        return $this->generateRandomString($length, $alphabet);
    }

    public function validate(array $data): bool
    {
        $frm = $data['frm_name'] ?? '';
        $separator = !empty($frm) ? $frm : self::SEPARATOR;
        $submittedTokenEncoded = $data['csrfToken'] ?? '';

        try {
            $decoded = $this->urlSafeDecode($submittedTokenEncoded);
            $part = explode($separator, $decoded);

            if (count($part) !== 3) {
                return false;
            }

            [$receivedHash, $baseToken, $timestamp] = $part;
            $timestamp = (int) $timestamp;
            $currentFrm = $data['frm_name'] ?? '';

            $currentTime = time();

            // Check expiration
            if (($timestamp + CSRF_TOKEN_LIFETIME) < $currentTime) {
                return false;
            }

            // Prevent future timestamps (clock skew tolerance)
            if ($timestamp > $currentTime + 60) {
                return false;
            }

            // Verify hash
            $expectedHash = hash_hmac(
                'sha256',
                session_id() . $baseToken . $timestamp . $currentFrm,
                CSRF_TOKEN_SECRET,
                true,
            );

            return hash_equals($expectedHash, $receivedHash);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getValue(): string
    {
        return $this->token;
    }

    public function getRememberHash(): string
    {
        return hash_hmac('sha256', $this->token, CSRF_TOKEN_SECRET);
    }

    /**
     * Generate an authentication token for API usage.
     */
    public function generateAuthToken(int $expiry = self::DEFAULT_AUTH_EXPIRY): string
    {
        // Generate token if not already set
        if (empty($this->token)) {
            $this->token = $this->generate();
        }

        // Calculate expiration time
        $expiryTime = time() + $expiry;

        // Create a hash of the token and expiry time
        $hash = hash_hmac('sha256', $this->token . $expiryTime, CSRF_TOKEN_SECRET, true);

        // Combine hash, token, and expiry into a single string
        $tokenData = $hash . '.' . $this->token . '.' . $expiryTime;

        // Return URL-safe encoded token
        return $this->urlSafeEncode($tokenData);
    }

    /**
     * Verify an authentication token.
     */
    public function verify(string $authToken): bool
    {
        try {
            // Split the token parts
            $decoded = $this->urlSafeDecode($authToken);
            $parts = explode('.', $decoded);

            if (count($parts) !== 3) {
                return false; // Invalid token format
            }

            [$receivedHash, $tokenValue, $expiry] = $parts;
            $expiry = (int) $expiry;

            // Check if token has expired
            if ($expiry < time()) {
                return false; // Token expired
            }

            // Create a new token with the extracted value
            $this->token = $tokenValue;

            // Compute the expected hash
            $expectedHash = hash_hmac('sha256', $tokenValue . $expiry, CSRF_TOKEN_SECRET, true);

            // Verify hash matches
            return hash_equals($expectedHash, $receivedHash);
        } catch (Throwable $e) {
            // Any exception means verification failed
            return false;
        }
    }

    /**
     * Generate a cryptographically secure random token for API keys.
     */
    public function generateApiKey(int $length = 32): string
    {
        $bytes = random_bytes($length);
        return $this->urlSafeEncode($bytes);
    }
}