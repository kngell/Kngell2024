<?php

declare(strict_types=1);

class RememberPreviousPageMiddleware implements MiddlewareInterface
{
    private const string SESSION_KEY = 'previous_url';

    private const array EXCLUDE_METHODS = [
        HttpMethod::POST,
        HttpMethod::PUT,
        HttpMethod::PATCH,
        HttpMethod::DELETE,
    ];

    public function __construct(
        private readonly NavigationHistoryService $navigationHistory,
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $this->navigationHistory->trackCurrentUrl(
            $request->getRequestedUri(),
            $request->getMethod(),
        );

        return $next->handle($request);
    }
}