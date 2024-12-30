<?php

declare(strict_types=1);
class LoggedInUsersCheckMiddleware extends AbstractMiddleware implements MiddlewareInterface
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
                $form = $this->frm->make('auth-user', $userData);
                $this->session->set('form', $form);
            }
            $this->session->set(PREVIOUS_PAGE, $request->getServer()->get('request_uri'));
            return $this->redirect('/login');
        }
        return $next->handle($request);
    }
}