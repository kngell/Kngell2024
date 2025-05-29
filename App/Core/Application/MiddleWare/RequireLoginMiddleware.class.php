<?php

declare(strict_types=1);
class RequireLoginMiddleware extends AbstractMiddleware implements MiddlewareInterface
{
    public function __construct(private FlashInterface $flash)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $session = $this->flash->getSession();
        if (! $session->exists(CURRENT_USER_SESSION_NAME)) {
            $this->flash->add('Please Login to access that page', FlashType::INFO);
            $session->set('current_url', $request->get('request_uri'));
            return new RedirectResponse('/login');
        }
        return $next->handle($request, []);
    }
}