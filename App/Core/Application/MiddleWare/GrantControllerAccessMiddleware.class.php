<?php

declare(strict_types=1);

class GrantControllerAccessMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RouteInfo $route,
        private AclService $aclService,
        private NavigationHistoryService $navigationHistory,
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $user = App::getInstance()->resolve('current.user');

        $controller = $this->route->getController();
        $action = $this->route->getMethod()->getName();

        // Special exemptions
        if ($this->isExemptRoute($request, $user)) {
            return $next->handle($request);
        }

        // Check access using central service
        if (!$this->aclService->hasAccess($user, $controller, $action)) {
            return $this->handleAccessDenied($request, $user);
        }

        return $next->handle($request);
    }

    private function isExemptRoute(Request $request, ?User $user): bool
    {
        $uri = $request->getRequestedUri();

        // Always allow logout for authenticated users
        if ($user && $uri === '/logout') {
            return true;
        }

        // Always allow access to login/error pages
        if (str_contains($uri, '/login') ||
            str_contains($uri, '/_restrict') ||
            str_contains($uri, '/_error')) {
            return true;
        }

        return false;
    }

    private function handleAccessDenied(Request $request, ?User $user): Response
    {
        $uri = $request->getRequestedUri();

        // Special case: Authenticated user on login page
        // if ($user && $uri === '/login') {
        //     $previousUrl = $this->session->get('previous_url');
        //     $this->session->delete('previous_url');
        //     return new RedirectResponse($previousUrl ?? '/');
        // }
        if ($user && $uri === '/login') {
            $url = $this->navigationHistory->getRedirectUrl();
            return new RedirectResponse($url ?? '/');
        }

        return new RedirectResponse('/_restrict');
    }
}