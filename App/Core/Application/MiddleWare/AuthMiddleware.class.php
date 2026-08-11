<?php

declare(strict_types=1);

class AuthMiddleware implements MiddlewareInterface
{
    private const array EXEMPT_PATHS = [
        '/login',
        '/register',
        '/forgot-password',
        '/reset-password',
        '/api/webhook',
        '/api/callback',
    ];

    private const array AUTH_REDIRECT_PATHS = [
        '/login' => '/',
        '/register' => '/',
        '/forgot-password' => '/',
    ];

    public function __construct(
        private UserContext $userContext,
        private NavigationHistoryService $navigationHistory,
        private ?array $options = [],
    ) {
        $this->options = array_merge([
            'login_route' => '/login',
        ], $options);
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $currentPath = $request->getPathFromUri();
        if ($this->userContext->isLoggedIn() && $this->isAuthPage($currentPath)) {
            $user = $this->userContext->currentUser();
            $redirectUrl = $this->navigationHistory->getRedirectUrl();
            return new RedirectResponse($redirectUrl);
        }

        if ($this->isExemptPath($currentPath)) {
            return $next->handle($request);
        }

        if (!$this->userContext->isLoggedIn()) {
            $this->navigationHistory->trackCurrentUrl($currentPath, $request->getMethod());
            $this->navigationHistory->markCurrentUrlAsInvalid();

            return new RedirectResponse($this->options['login_route']);
        }
        return $next->handle($request);
    }

    private function isAuthPage(string $path): bool
    {
        return isset(self::AUTH_REDIRECT_PATHS[$path]);
    }

    private function isExemptPath(string $path): bool
    {
        foreach (self::EXEMPT_PATHS as $exemptPath) {
            if (str_starts_with($path, $exemptPath)) {
                return true;
            }
        }
        return false;
    }
}