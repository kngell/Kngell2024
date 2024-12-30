<?php

declare(strict_types=1);

class RememberPreviousPageMiddleware implements MiddlewareInterface
{
    public function __construct(private SessionInterface $session)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        $previous = $this->session->get(PREVIOUS_PAGE);
        $current = $this->session->get(CURRENT_PAGE);
        $url = $request->getServer()->get('request_uri');
        $referrer = $request->getServer()->get('http_referer');
        $method = $request->getMethod()->name;
        if ($request->getMethod()->name !== 'POST') {
            $s = $_SESSION;
            // $url = $request->getServer()->get('request_uri');
            $this->session->set(PREVIOUS_PAGE, $current);
            $this->session->set(CURRENT_PAGE, $url);
        }
        return $next->handle($request);
    }
}