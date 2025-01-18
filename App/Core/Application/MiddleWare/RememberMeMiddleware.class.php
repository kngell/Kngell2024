<?php

declare(strict_types=1);
class RememberMeMiddleware extends AbstractMiddleware implements MiddlewareInterface
{
    public function __construct(private SessionInterface $session, private FlashInterface $flash, private UserFormCreator $frm)
    {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        if (! $this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $user = AuthService::currentUser();
            if ($user !== null) {
                $this->flash->add('Wellcome back ' . $user->getFirstName() . ' Please Log in');
                $userData = [
                    'email' => $user->getEmail(),
                    'remember_me' => 'on',
                ];
                if (! $this->session->exists('form')) {
                    $form = $this->frm->make('auth-user', $userData);
                } else {
                    $form = $this->session->get('form');
                    $this->session->delete('form');
                }

                $this->session->set('form', $form);
            }
        }
        return $next->handle($request);
    }
}