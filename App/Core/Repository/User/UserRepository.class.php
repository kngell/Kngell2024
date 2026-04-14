<?php

declare(strict_types=1);

class UserRepository extends Repository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function findByToken(string $authToken): ?User
    {
        try {
            // Extract token value from auth token
            $tokenValue = $this->extractTokenValue($authToken);

            if (!$tokenValue) {
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

    private function extractTokenValue(string $authToken): ?string
    {
        try {
            $token = new Token();
            if (!$token->verify($authToken)) {
                return null;
            }
            $decoded = $token->urlSafeDecode($authToken);
            $parts = explode('.', $decoded);

            if (count($parts) !== 3) {
                return null;
            }

            return $parts[1];
        } catch (Exception $e) {
            return null;
        }
    }
}