<?php

declare(strict_types=1);

class RememberPreviousPageMiddleware implements MiddlewareInterface
{
    private const array AUTH_URI = ['/login', '/logout', '/reister'];

    public function __construct(private SessionInterface $session)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        $response = $next->handle($request);
        $previous = $this->session->get(PREVIOUS_PAGE);
        $current = $this->session->get(CURRENT_PAGE);
        if ($request->getMethod()->name !== 'POST' && ! in_array($current, self::AUTH_URI)) {
            $url = $request->getServer()->get('request_uri');
            $this->session->set(PREVIOUS_PAGE, $current);
            $this->session->set(CURRENT_PAGE, $url);
        }
        return $response;
    }
}