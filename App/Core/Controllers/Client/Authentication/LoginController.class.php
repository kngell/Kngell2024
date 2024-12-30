<?php

declare(strict_types=1);

class LoginController extends AuthController
{
    public function __construct(
        UsersModel $users,
        UserSessionModel $userSession,
        CookieInterface $cookie,
        private UserFormCreator $frm,
        private Validator $validator,
        private HtmlBuilder $html,
        HashInterface $hash,
    ) {
        parent::__construct($users, $userSession, $cookie, $hash);
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
        return $this->render('index', array_merge([
            'authForm' => $form,
        ]));
    }

    public function authenticateUser() : Response
    {
        [$userData, $errors] = $this->userData();
        if (! empty($errors)) {
            $this->flash->add('Fields Errors... Please check.', FlashType::WARNING);
            return $this->redirect(DS . 'login');
        }
        $user = $this->authenticate($userData);
        if (! $user) {
            $this->flash->add('The authentication failed! Please create an account.', FlashType::WARNING);
            return $this->redirect('/login');
        }
        if (! $this->loginUser($user, $userData)) {
            $this->flash->add('Login failed! Please try again', FlashType::WARNING);
            return $this->redirect('/login');
        }
        return $this->redirect($this->session->get(PREVIOUS_PAGE) ?? '/');
    }

    private function userData() : array
    {
        $userData = $this->request->getPost()->getAll();
        $errors = $this->validator->validate($userData, 'login', $this->users);
        $form = $this->frm->make('auth-user', $userData, $errors);
        if (! $this->session->exists('form')) {
            $this->session->set('form', $form);
        }
        return [$userData, $errors];
    }
}