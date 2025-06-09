<?php

declare(strict_types=1);

class UserRepository extends Repository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    /**
     * Find a user by their authentication token.
     *
     * This method extracts the token value from the auth token and uses it
     * to look up the user in the database.
     *
     * @param string $authToken The authentication token
     * @return User|null The user if found, null otherwise
     */
    public function findByToken(string $authToken): ?User
    {
        try {
            // Extract token value from auth token
            $tokenValue = $this->extractTokenValue($authToken);

            if (! $tokenValue) {
                return null;
            }

            // Create token object to generate hash
            $token = new Token($tokenValue);
            $tokenHash = $token->getRememberHash();

            // Find user by token hash
            $criteria = ['activation_hash' => $tokenHash];

            // The token should not be expired
            $criteria[] = ['token_expire', '>', date('Y-m-d H:i:s')];

            // Only active and verified users
            $criteria['active'] = 1;
            $criteria['verified'] = 1;

            return $this->findOneBy($criteria);
        } catch (Exception $e) {
            // Log the error but don't expose it
            return null;
        }
    }

    /**
     * Extract token value from the auth token.
     *
     * @param string $authToken The authentication token
     * @return string|null The token value or null if invalid
     */
    private function extractTokenValue(string $authToken): ?string
    {
        try {
            // Create token object to decode and validate
            $token = new Token();

            // Check if token is valid
            if (! $token->verify($authToken)) {
                return null;
            }

            // Extract the token part
            $decoded = $token->urlSafeDecode($authToken);
            $parts = explode('.', $decoded);

            if (count($parts) !== 3) {
                return null;
            }

            return $parts[1]; // Return the token value (middle part)
        } catch (Exception $e) {
            return null;
        }
    }
}