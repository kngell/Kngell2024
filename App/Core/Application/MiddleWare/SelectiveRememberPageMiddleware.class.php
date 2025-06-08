<?php

declare(strict_types=1);

/**
 * Alternative approach: Only remember pages for specific route patterns
 * Apply this middleware only to routes where you want to remember the previous page.
 */
class SelectiveRememberPageMiddleware implements MiddlewareInterface
{
    private const string SESSION_KEY = 'previous_url';

    // Only remember these types of pages
    private const array REMEMBER_PATTERNS = [
        '/products',
        '/home',
        '/',
        '/blog',
        '/categories',
        '/search',
        // Add other browsable/content pages
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
        return $this->isRememberablePage($uri) && ! $this->isExcludedMethod($method);
    }

    private function isRememberablePage(string $uri): bool
    {
        foreach (self::REMEMBER_PATTERNS as $pattern) {
            if (str_starts_with($uri, $pattern)) {
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