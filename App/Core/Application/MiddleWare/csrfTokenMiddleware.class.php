<?php

declare(strict_types=1);

class CsrfTokenMiddleware implements MiddlewareInterface
{
    use AjaxResponseTrait;
    use HtmlPageCacheableTrait;
    use CacheInvalidationTrait;

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
    ];

    public function __construct(
        private readonly TokenInterface $token,
        private readonly FlashInterface $flash,
        private FileUploadFactory $uploader,
        private FormDataHandlerService $formDataHandler,
        private NavigationHistoryService $navigationHistory,
        private HtmlPageCacheFactory $pageCacheFactory,
        private RouteMatchingService $routeMatchingService,
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

    private function handleCsrfFailure(Request $request): Response
    {
        $postData = $request->getPost()->getAll();
        unset($postData['csrfToken']);

        $formData = $this->formDataHandler->prepareForValidation($postData);
        $webPaths = $this->formDataHandler->extractWebPathsFromForm($formData);

        $uploadService = $this->uploader->create($request, [], $webPaths);
        $uploadService->proceed(false, true);

        $allMediaPaths = $uploadService->getMediaPathsByField();

        $this->flash->addFormData(
            $request->getRequestedUri(),
            $formData,
            [],
            $allMediaPaths,
        );
        $referedUrl = $this->navigationHistory->getRedirectUrl();
        $routes = $this->routeMatchingService->getRoutes();
        $routeInfos = $this->routeMatchingService->findRouteForPath($referedUrl, $routes);

        if ($routeInfos) {
            $controller = ucfirst($routeInfos['controller']) . 'Controller';
            $method = $routeInfos['action'];
            $this->initializeHtmlCache($this->pageCacheFactory);
            $this->invalidateCache($controller, $method);
        }

        $message = 'Security token mismatch. Your uploaded files have been preserved. Please try again.';
        $this->flash->add($message, FlashType::DANGER);

        $redirectUrl = $this->navigationHistory->getIntendedUrl();
        if ($request->isAjax()) {
            return $this->respondError(
                isAjax: $request->isAjax(),
                message: $message,
                redirect: $referedUrl ?? $referedUrl,
                flashType: FlashType::DANGER,
                statusCode: HttpStatusCode::HTTP_PAGE_EXPIRED_LARAVEL_FRAMEWORK,
                extraData: [
                    'intended_route' => $request->getRequestedUri(),
                    'reason' => 'token_mismatch',
                ],
            );
        }
        return new RedirectResponse($redirectUrl ?? $referedUrl, 302);
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

        if (!isset($postData['csrfToken']) || empty($postData['csrfToken'])) {
            return false;
        }

        return $this->token->validate($postData);
    }
}