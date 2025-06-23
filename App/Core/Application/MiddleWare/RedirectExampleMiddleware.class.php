<?php

declare(strict_types=1);

class RedirectExampleMiddleware implements MiddlewareInterface
{
    public function __construct(private Response $response)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        //Auth : check the session to see if there is a value set there
        $this->response->redirect('/products/index');
        $this->response->setStatusCode(HttpStatusCode::HTTP_MOVED_PERMANENTLY);
        return $this->response;
    }
}