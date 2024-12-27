<?php

declare(strict_types=1);

class LoginController extends AuthenticationController
{
    public function __construct(
        UsersModel $users,
        private UserFormCreator $frm,
        private Validator $validator,
        HashInterface $hash,
        FlashInterface $flash
    ) {
        parent::__construct($users, $hash, $flash);
    }

    public function before() : void
    {
        echo 'this is before';
    }

    public function index() : string
    {
        $this->pageTitle('Login');
        $session = $this->token->getSession();
        if ($session->exists('form')) {
            $form = $session->get('form');
            $session->delete('form');
        } else {
            $form = $this->frm->make('auth-user');
        }
        return $this->render('index', ['authForm' => $form, 'message' => $this->flash->get()]);
    }

    public function authenticateUser() : Response
    {
        [$userData, $errors, $session] = $this->userData();
        if (! empty($errors)) {
            $this->flash->add('Fields Errors... Please check.', FlashType::WARNING);
            return $this->redirect(DS . 'login');
        }
        $user = $this->authenticate($userData);
        if (! $user) {
            $this->flash->add('The authentication failed! Please create an account before login In.', FlashType::WARNING);
            return $this->redirect('/login');
        }
        if (! $this->loginUser($user)) {
            $this->flash->add('Login failed! Please try again', FlashType::WARNING);
        }
        return $this->redirect($session->get(PREVIOUS_URL_KEY) ?? '/');
    }

    private function userData() : array
    {
        $userData = $this->request->getPost()->getAll();
        $errors = $this->validator->validate($userData, 'login', $this->users);
        $form = $this->frm->make('auth-user', $userData, $errors);
        $session = $this->token->getSession();
        if (! $session->exists('form')) {
            $session->set('form', $form);
        }
        return [$userData, $errors, $session];
    }
}