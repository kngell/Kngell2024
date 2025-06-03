<?php

declare(strict_types=1);

class GetUserCartFromCookieMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CookieInterface $cookie,
        private SessionInterface $session
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $s = $_SESSION;
        if (! $this->session->exists('cart')) {
        }
        return $next->handle($request);
    }
}