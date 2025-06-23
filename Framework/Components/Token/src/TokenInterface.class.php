<?php

declare(strict_types=1);

interface TokenInterface
{
    /**
     * Generate a CSRF token hash.
     */
    public function getCsrfHash(int $length = 8, string $frm = '', string $alphabet = '') : string;

    /**
     * Get a hash for remember-me functionality.
     */
    public function getRememberHash(): string;

    /**
     * Generate a random token of specified length.
     */
    public function generate(int $length = CSRF_TOKEN_LENGHT, string $alphabet = '') : string;

    /**
     * Validate CSRF token data.
     */
    public function validate(array $data) : bool;

    /**
     * Decode a URL-safe string.
     */
    public function urlSafeDecode(string $str) : string;

    /**
     * Encode a string to make it URL-safe.
     */
    public function urlSafeEncode(string $str) : string;

    /**
     * Get the token value.
     */
    public function getValue(): string;

    /**
     * Verify an authentication token.
     *
     * This method verifies if the provided token is valid and not expired.
     * Format of token: [hash].[token].[expiry]
     *
     * @param string $authToken The authentication token to verify
     * @return bool True if token is valid, false otherwise
     */
    public function verify(string $authToken): bool;

    /**
     * Generate an authentication token for API usage.
     *
     * @param int $expiry Expiration time in seconds from now
     * @return string The encoded authentication token
     */
    public function generateAuthToken(int $expiry = 3600): string;
}