<?php

declare(strict_types=1);

class CsrfTokenMiddleware implements MiddlewareInterface
{
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
        '/upload-cleanup/cleanup',
        // Add other paths that should be exempt from CSRF protection
    ];

    public function __construct(
        private readonly TokenInterface $token,
        private readonly FlashInterface $flash,
        private array $safeRedirectExclude,
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        if ($this->requiresCsrfValidation($request)) {
            if (!$this->validateCsrfToken($request)) {
                return $this->handleCsrfFailure($request);
            }
        }
    
        return $next->handle($request);
    }

    private function requiresCsrfValidation(Request $request): bool
    {
        return $this->isStateChangingMethod($request->getMethod())
            && !$this->isExemptPath($request->getRequestedUri());
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

        if (empty($postData)) {
            return false;
        }

        // Check if CSRF token exists in the request
        if (!isset($postData['csrfToken']) || empty($postData['csrfToken'])) {
            return false;
        }

        return $this->token->validate($postData);
    }

    private function handleCsrfFailure(Request $request): RedirectResponse
    {
        $postData = $request->getPost()->getAll();

        $this->flash->addFormData(
            $request->getRequestedUri(),
            $postData,
            [],
            $request->getFiles()->all(),
        );

        $this->flash->add(
            'Security token mismatch or expired. Please try submitting the form again.',
            FlashType::DANGER,
        );

        $redirectUrl = $this->determineRedirectUrl($request);

        return new RedirectResponse($redirectUrl);
    }

    private function determineRedirectUrl(Request $request): string
    {
        $currentUri = $request->getRequestedUri();

        if ($this->isSafeRedirectUrl($currentUri)) {
            return $currentUri;
        }
        $session = $this->flash->getSession();
        $previousUrl = $session->get('previous_url');

        if ($previousUrl && $this->isSafeRedirectUrl($previousUrl)) {
            return $previousUrl;
        }
        return self::DEFAULT_REDIRECT_URL;
    }

    // isSafeRedirectUrl (REFINED)
    private function isSafeRedirectUrl(string $url): bool
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return !$this->isExcludedPath($url);
        }

        $parsedUrl = parse_url($url);
        if ($parsedUrl === false || !isset($parsedUrl['host'])) {
            return false;
        }

        $currentHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';

        // Check if the scheme and host match
        if ($parsedUrl['host'] !== $currentHost) {
            return false;
        }

        $path = $parsedUrl['path'] ?? '/';
        return !$this->isExcludedPath($path);
    }

    private function isExcludedPath(string $uri): bool
    {
        $excludePaths = $this->safeRedirectExclude;

        foreach ($excludePaths as $path) {
            if (str_starts_with($uri, $path)) {
                return true;
            }
        }
        return false;
    }
}