<?php

declare(strict_types=1);

class LoginController extends AuthController
{
    public function __construct(
        UserModel $users,
        UserSessionModel $userSession,
        CookieInterface $cookie,
        private UserFormCreator $frm,
        private ValidatorInterface $validator,
        private HtmlBuilder $html,
        HashInterface $hash,
        LoginAttemptsModel $loginAttempts
    ) {
        parent::__construct($users, $userSession, $cookie, $hash, $loginAttempts);
    }

    public function before() : void
    {
        echo 'this is before';
    }

    public function index() : string
    {
        $this->pageTitle('Login');
        if ($this->session->exists('form')) {
            $form = $this->session->get('form');
            $this->session->delete('form');
        } else {
            $form = $this->frm->make('auth-user');
        }
        return $this->render('index', [
            'authForm' => $form,
        ]);
    }

    public function authenticateUser() : Response
    {
        [$userData, $errors] = $this->validateUserData();
        if (! empty($errors)) {
            $this->flash->add('Fields Errors... Please check.', FlashType::WARNING);
        }
        if (! $this->isUserAuthenticated($userData)) {
            return new RedirectResponse('/login');
        }
        return new RedirectResponse($this->getRedirectUrl() ?? '/');
    }

    private function validateUserData() : array
    {
        $userData = $this->request->getPost()->getAll();
        $errors = $this->validator->validate($userData, 'login', $this->user);
        $form = $this->frm->make('auth-user', $userData, $errors);
        if (! $this->session->exists('form')) {
            $this->session->set('form', $form);
        }
        return [$userData, $errors];
    }
}
