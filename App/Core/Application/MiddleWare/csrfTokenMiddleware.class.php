<?php

declare(strict_types=1);

class CsrfTokenMiddleware implements MiddlewareInterface
{
    private const string PREVIOUS_URL_SESSION_KEY = 'previous_url';
    private const string DEFAULT_REDIRECT_URL = '/';

    private const array STATE_CHANGING_METHODS = [
        HttpMethod::POST,
        HttpMethod::PUT,
        HttpMethod::PATCH,
        HttpMethod::DELETE,
    ];

    private const array CSRF_EXEMPT_PATHS = [
        '/api/webhook',
        '/api/callback',
        // Add other paths that should be exempt from CSRF protection
    ];

    public function __construct(
        private readonly TokenInterface $token,
        private readonly FlashInterface $flash
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        if ($this->requiresCsrfValidation($request)) {
            if (! $this->validateCsrfToken($request)) {
                return $this->handleCsrfFailure($request);
            }
        }

        return $next->handle($request);
    }

    private function requiresCsrfValidation(Request $request): bool
    {
        return $this->isStateChangingMethod($request->getMethod())
            && ! $this->isExemptPath($request->getRequestedUri());
    }

    private function isStateChangingMethod(HttpMethod $method): bool
    {
        return in_array($method, self::STATE_CHANGING_METHODS, true);
    }

    private function isExemptPath(string $uri): bool
    {
        foreach (self::CSRF_EXEMPT_PATHS as $exemptPath) {
            if (str_starts_with($uri, $exemptPath)) {
                return true;
            }
        }
        return false;
    }

    private function validateCsrfToken(Request $request): bool
    {
        $postData = $request->getPost()->getAll();

        // Check if CSRF token exists in the request
        if (! isset($postData['csrfToken']) || empty($postData['csrfToken'])) {
            return false;
        }

        return $this->token->validate($postData);
    }

    private function handleCsrfFailure(Request $request): RedirectResponse
    {
        $this->flash->add(
            'Security token mismatch. Please try submitting the form again.',
            FlashType::DANGER
        );

        $redirectUrl = $this->determineRedirectUrl($request);

        return new RedirectResponse($redirectUrl);
    }

    private function determineRedirectUrl(Request $request): string
    {
        $session = $this->flash->getSession();

        // Try to get the previous URL from session
        $previousUrl = $session->get(self::PREVIOUS_URL_SESSION_KEY);

        // If no previous URL or it's the same as current (to avoid redirect loops)
        if (! $previousUrl || $previousUrl === $request->getRequestedUri()) {
            return self::DEFAULT_REDIRECT_URL;
        }

        // Validate that the previous URL is safe (same origin)
        if ($this->isSafeRedirectUrl($previousUrl)) {
            return $previousUrl;
        }

        return self::DEFAULT_REDIRECT_URL;
    }

    private function isSafeRedirectUrl(string $url): bool
    {
        // Only allow relative URLs or URLs from the same domain
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        // For absolute URLs, check if they're from the same domain
        $parsedUrl = parse_url($url);
        if ($parsedUrl === false || ! isset($parsedUrl['host'])) {
            return false;
        }

        // Get current domain from server variables
        $currentHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';

        return $parsedUrl['host'] === $currentHost;
    }
}