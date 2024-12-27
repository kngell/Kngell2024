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
            $session->set(PREVIOUS_URL_KEY, $request->getServer()->get('request_uri'));
            return $this->controller()->redirect('/login');
        }
        return $next->handle($request, []);
    }

    private function Controller() : Controller
    {
        $app = App::getInstance();
        return (new ControllerMiddleware())->setRequest($app->getRequest())
            ->setView($app->get(ViewInterface::class))
            ->setresponse($app->getResponse())
            ->setToken($app->get(TokenInterface::class));
    }
}