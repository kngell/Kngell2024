<?php

declare(strict_types=1);

class CsrfTokenMiddleware implements MiddlewareInterface
{
    public function __construct(private TokenInterface $token)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        $data = $request->getPost()->getAll();
        // $session = $this->token->getSession();
        // $s = $_SESSION;
        // if ($session->exists('form')) {
        //     $form = $session->get('form');
        //     $session->delete('form');
        //     $app = App::getInstance();
        //     $response = $app->getResponse();
        //     return $response->redirect('/home/form');
        // }
        /* @var Response */
        return $next->handle($request);
    }
}