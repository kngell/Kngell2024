<?php

declare(strict_types=1);
class Token extends RandomStringGenerator implements TokenInterface
{
    private string $token;
    private string $tokenHash;

    public function __construct(string|null $token = null)
    {
        if ($token === null) {
            $this->token = $this->generate();
        } else {
            $this->token = $token;
        }
    }

    public function getCsrfHash(int $length = CSRF_TOKEN_LENGHT, string $frm = '', string $alphabet = '') : string
    {
        $time = time();
        $separator = ! empty($frm) ? $frm : '|';
        $hash = hash_hmac('sha256', session_id() . $this->token . $time . $frm, CSRF_TOKEN_SECRET, true);
        return $this->urlSafeEncode($hash . $separator . $this->token . $separator . $time);
    }

    public function generate(int $length = CSRF_TOKEN_LENGHT, string $alphabet = '') : string
    {
        $token = '';
        $this->setAlphabet($alphabet);
        for ($i = 0; $i < $length; $i++) {
            $randomKey = $this->getRandomInteger(0, $this->alphabetLength);
            $token .= $this->alphabet[$randomKey];
        }
        return $token;
    }

    public function validate(array $data) : bool
    {
        $frm = $data['frm_name'];
        $separator = ! empty($frm) ? $frm : '|';
        $part = explode($separator, $this->urlSafeDecode($data['csrfToken'] ?? ''));
        if (count($part) === 3) {
            $hash = hash_hmac('sha256', session_id() . $part[1] . $part[2] . ($data['frm_name'] ?? ''), CSRF_TOKEN_SECRET, true);
            if (hash_equals($hash, $part[0])) {
                $this->tokenHash = $hash;
                return true;
            }
        }
        return false;
    }

    public function urlSafeEncode(string $str) : string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    public function urlSafeDecode(string $str) : string
    {
        return base64_decode(strtr($str, '-_', '+/'));
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getRememberHash(): string
    {
        return hash_hmac('sha256', $this->token, CSRF_TOKEN_SECRET);
    }

    /**
     * Verify an authentication token.
     *
     * This method verifies if the provided token is valid and not expired.
     * Format of token: [hash].[token].[expiry]
     *
     * @param string $authToken The authentication token to verify
     * @return bool True if token is valid, false otherwise
     */
    public function verify(string $authToken): bool
    {
        try {
            // Split the token parts
            $parts = explode('.', $this->urlSafeDecode($authToken));

            if (count($parts) !== 3) {
                return false; // Invalid token format
            }

            [$receivedHash, $tokenValue, $expiry] = $parts;

            // Check if token has expired
            if ((int) $expiry < time()) {
                return false; // Token expired
            }

            // Create a new token with the extracted value
            $this->token = $tokenValue;

            // Compute the expected hash
            $expectedHash = hash_hmac('sha256', $tokenValue . $expiry, CSRF_TOKEN_SECRET, true);

            // Verify hash matches
            return hash_equals($expectedHash, $receivedHash);
        } catch (Exception $e) {
            // Any exception means verification failed
            return false;
        }
    }

    /**
     * Generate an authentication token for API usage.
     *
     * @param int $expiry Expiration time in seconds from now (default: 1 hour)
     * @return string The encoded authentication token
     */
    public function generateAuthToken(int $expiry = 3600): string
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
}