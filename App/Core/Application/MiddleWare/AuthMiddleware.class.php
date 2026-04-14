<?php

declare(strict_types=1);

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SessionInterface $session,
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $user = $this->authenticateViaToken($request);

        if (!$user) {
            $user = $this->authenticateViaSession();
        }
        App::getInstance()->instance('current.user', $user);
        return $next->handle($request);
    }

    /**
     * Authenticate user via token (useful for API requests).
     */
    private function authenticateViaToken(Request $request): ?User
    {
        return App::diCall(function (
            TokenInterface $token,
            UserRepository $users,
        ) use ($request) {
            // Check for token in various locations:
            // 1. Authorization header
            // 2. Bearer token format
            // 3. Custom header
            // 4. Query parameter
            $authToken = $this->extractAuthToken($request);

            if ($authToken && $token->verify($authToken)) {
                return $users->findByToken($authToken);
            }
            return null;
        });
    }

    /**
     * Extract authentication token from various locations in the request.
     */
    private function extractAuthToken(Request $request): ?string
    {
        // Check Authorization header (standard)
        $authHeader = $request->get('authorization');
        if ($authHeader) {
            // If it's a Bearer token, extract the token part
            if (strpos($authHeader, 'Bearer ') === 0) {
                return substr($authHeader, 7);
            }
            return $authHeader;
        }

        // Check custom X-API-Token header
        $apiToken = $request->get('x-api-token');
        if ($apiToken) {
            return $apiToken;
        }

        // Check query parameter (less secure, but sometimes needed)
        $queryToken = $request->get('token');
        if ($queryToken) {
            return $queryToken;
        }

        return null;
    }

    /**
     * Authenticate user via session (useful for web requests).
     */
    private function authenticateViaSession(): ?User
    {
        return AuthService::currentUser();
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            return AuthService::currentUser();
        }
        return null;
    }
}