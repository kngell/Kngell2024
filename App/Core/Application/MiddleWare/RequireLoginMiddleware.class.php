<?php

declare(strict_types=1);
class RequireLoginMiddleware extends AbstractMiddleware implements MiddlewareInterface
{
    public function __construct(private RouteInfo $route, private FlashInterface $flash, private CacheInterface $cache)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $session = $this->flash->getSession();
        $currentUri = $request->get('request_uri');
        if (! $session->exists(CURRENT_USER_SESSION_NAME)) {
            $this->flash->add('Please Login to access that page', FlashType::INFO);

            if ($request->getMethod() !== HttpMethod::POST) {
                $session->set('current_url', $currentUri);
            }
            return new RedirectResponse('/login');
        }
        return $next->handle($request, []);
    }
}