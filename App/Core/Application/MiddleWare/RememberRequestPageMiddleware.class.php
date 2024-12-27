<?php

declare(strict_types=1);

class RememberRequestPageMiddleware implements MiddlewareInterface
{
    public function __construct(private SessionInterface $session)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        if ($request->getMethod() !== 'POST') {
            $url = $request->getServer()->get('request_uri');
            $this->session->set(PREVIOUS_URL_KEY, $request->getServer()->get('request_uri'));
        }
        return $next->handle($request);
    }
}