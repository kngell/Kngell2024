<?php

declare(strict_types=1);
class RequireLoginMiddleware implements MiddlewareInterface
{
    public function __construct(private FlashInterface $flash)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $session = $this->flash->getSession();
        if (! $session->exists(CURRENT_USER_SESSION_NAME)) {
            $this->flash->add('Please Login to access that page', FlashType::INFO);
            $session->set(PREVIOUS_PAGE, $request->getServer()->get('request_uri'));
            return $this->redirect('/login');
        }
        return $next->handle($request, []);
    }
}