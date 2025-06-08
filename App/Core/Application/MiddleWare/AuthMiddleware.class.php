<?php

declare(strict_types=1);

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserService $userService,
        private SessionInterface $session,
        private Logger $logger
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $user = App::diCall(function (
            TokenInterface $token,
            UserRepository $users
        ) use ($request) {
            $authToken = $request->get('authorization');
            if ($authToken && $token->verify($authToken)) {
                return $users->findByToken($authToken);
            }
            return null;
        });

        // Make user available for injection in controllers (even if null)
        App::getInstance()->instance('current.user', $user);

        return $next->handle($request);
    }
}