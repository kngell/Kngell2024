<?php

declare(strict_types=1);

class RememberPreviousPageMiddleware implements MiddlewareInterface
{
    private const array EXCLUDE_URI = [
        '/login',
        '/logout',
        '/auth-user',
        '/_restrict',
        '/.well-known/appspecific/com.chrome.devtools.json',
    ];

    public function __construct(private SessionInterface $session)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        $url = $request->get('request_uri');
        if (! in_array($url, self::EXCLUDE_URI) && ! str_contains($url, '/edit')) {
            $this->session->set('previous_url', $url);
        }
        return $next->handle($request);
    }
}