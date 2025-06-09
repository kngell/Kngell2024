<?php

declare(strict_types=1);

/**
 * RequireLoginMiddleware - Enforces user login for protected routes.
 *
 * This middleware is responsible for:
 * 1. Checking if a user is authenticated
 * 2. Redirecting to login page if not authenticated
 * 3. Saving the current URL for post-login redirection
 */
class RequireLoginMiddleware extends AbstractMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RouteInfo $route,
        private FlashInterface $flash,
        private CacheInterface $cache
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        // Check if we have a current user (should be set by AuthMiddleware)
        $user = App::getInstance()->resolve('current.user');

        if (! $user) {
            return $this->handleUnauthenticatedUser($request);
        }

        return $next->handle($request, []);
    }

    /**
     * Handle unauthenticated user access to protected routes.
     */
    private function handleUnauthenticatedUser(Request $request): Response
    {
        $session = $this->flash->getSession();
        $currentUri = $request->get('request_uri');

        $this->flash->add('Please Login to access that page', FlashType::INFO);

        // Save current URL for redirection after login (only for GET requests)
        if ($request->getMethod() !== HttpMethod::POST) {
            $session->set('current_url', $currentUri);
        }

        return new RedirectResponse('/login');
    }
}