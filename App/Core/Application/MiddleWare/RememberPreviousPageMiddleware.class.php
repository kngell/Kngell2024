<?php

declare(strict_types=1);

class RememberPreviousPageMiddleware implements MiddlewareInterface
{
    private const string SESSION_KEY = 'previous_url';

    // Authentication & Security Pages
    private const array AUTH_EXCLUDE_PATHS = [
        '/login',
        '/logout',
        '/auth-user',
        '/signup',
        '/register',
        '/forgot',
        '/reset-pw',
        '/forgot-pw',
        '/password/reset/',
        '/signup/activate/',
    ];

    // API & System Endpoints
    private const array API_EXCLUDE_PATHS = [
        '/api',
        '/_restrict',
        '/_404_error',
        '/_500_error',
        '/_client_error',
        '/_dev_error',
        '/restore-cart',
        '/user-from-cookie',
    ];

    // Payment & Sensitive Operations
    private const array PAYMENT_EXCLUDE_PATHS = [
        '/create-payment',
    ];

    // Static Assets & Development Tools
    private const array ASSET_EXCLUDE_PATHS = [
        '/public/assets/',
        '/.well-known/',
        '/assets/',
    ];

    private const array EXCLUDE_METHODS = [
        HttpMethod::POST,
        HttpMethod::PUT,
        HttpMethod::PATCH,
        HttpMethod::DELETE,
    ];

    public function __construct(private readonly SessionInterface $session)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $currentUri = $request->getRequestedUri();

        if ($this->shouldRememberUrl($currentUri, $request->getMethod())) {
            $this->session->set(self::SESSION_KEY, $currentUri);
        }

        return $next->handle($request);
    }

    private function shouldRememberUrl(string $uri, HttpMethod $method): bool
    {
        return ! $this->isExcludedUri($uri) && ! $this->isExcludedMethod($method);
    }

    private function isExcludedUri(string $uri): bool
    {
        return $this->matchesAnyPath($uri, self::AUTH_EXCLUDE_PATHS)
            || $this->matchesAnyPath($uri, self::API_EXCLUDE_PATHS)
            || $this->matchesAnyPath($uri, self::PAYMENT_EXCLUDE_PATHS)
            || $this->matchesAnyPath($uri, self::ASSET_EXCLUDE_PATHS);
    }

    private function matchesAnyPath(string $uri, array $paths): bool
    {
        foreach ($paths as $path) {
            if (str_starts_with($uri, $path)) {
                return true;
            }
        }
        return false;
    }

    private function isExcludedMethod(HttpMethod $method): bool
    {
        return in_array($method, self::EXCLUDE_METHODS, true);
    }
}
