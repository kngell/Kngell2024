<?php

declare(strict_types=1);

class RememberPreviousPageMiddleware implements MiddlewareInterface
{
    private const array EXCLUDE_URI = [
        '/login',
        '/logout',
        '/auth-user',
        '/_restrict',
        '/create-payment',
        '/.well-known/appspecific/com.chrome.devtools.json',
        '/public/assets/commons/frontend/ckeditor5.css.map',
    ];

    public function __construct(private SessionInterface $session)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        $url = $request->get('request_uri');
        $previous = $this->session->get('previous_url');
        if (! in_array($url, self::EXCLUDE_URI) && $request->getMethod() !== HttpMethod::POST) {
            $this->session->set('previous_url', $url);
        }
        return $next->handle($request);
    }
}