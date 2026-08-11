<?php

declare(strict_types=1);
class GrantControllerAccessMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RouteInfo $route,
        private AclService $aclService,
        private UserContext $userContext,
        private NavigationHistoryService $navigationHistory,
        private ?array $options = [],
    ) {
        $this->options = array_merge([
            'unauthorized_route' => '/_restrict',
            'login_route' => '/login',
        ], $options);
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $user = $this->userContext->currentUser();
        if ($this->isExemptRoute($request, $user)) {
            return $next->handle($request);
        }

        $controller = $this->getControllerName();
        $action = $this->getActionName();

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

        if ($user && $uri === '/login') {
            $url = $this->navigationHistory->getRedirectUrl();
            return new RedirectResponse($url);
        }

        // For AJAX requests, return 403
        if ($request->isAjax()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Unauthorized access',
            ], HttpStatusCode::HTTP_FORBIDDEN);
        }

        // Mark current URL as invalid to avoid redirect loops
        $this->navigationHistory->markCurrentUrlAsInvalid();

        // Redirect to unauthorized page
        return new RedirectResponse($this->options['unauthorized_route']);
    }

    /**
     * Get controller name from RouteInfo.
     */
    private function getControllerName(): string
    {
        $controller = $this->route->getController();
        return basename(str_replace('\\', '/', $controller));
    }

    /**
     * Get action name from RouteInfo.
     */
    private function getActionName(): string
    {
        $method = $this->route->getMethod();
        return $method ? $method->getName() : 'index';
    }
}