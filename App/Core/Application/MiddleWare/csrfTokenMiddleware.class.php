<?php

declare(strict_types=1);

class CsrfTokenMiddleware implements MiddlewareInterface
{
    public function __construct(private TokenInterface $token, private FlashInterface $flash)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next) : Response|string
    {
        if ($request->getMethod() === HttpMethod::POST && ! $this->token->validate($request->getPost()->getAll())) {
            $this->flash->add('CsrfToken mismatch! Please submit the form again', FlashType::WARNING);
            $previousUrl = $this->flash->getSession()->get('previous_url');
            return new RedirectResponse($previousUrl ?? '/');
        }
        return $next->handle($request);
    }
}