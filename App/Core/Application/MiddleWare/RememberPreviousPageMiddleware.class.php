<?php

declare(strict_types=1);

class RememberPreviousPageMiddleware implements MiddlewareInterface
{
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