<?php

declare(strict_types=1);

class UsersController extends Controller
{
    public function __construct(private UsersModel $user, private UserFormCreator $frm, private Validator $validator, private FlashInterface $flash)
    {
    }

    public function index(string|null $form = null) : string
    {
        if ($form === null) {
            $form = $this->frm->make('login');
        }
        return $this->render('index', ['authForm' => $form]);
    }

    public function login() : string
    {
        $data = $this->request->getPost()->getAll();
        $errors = $this->validator->validate($data, 'login', $this->user);
        if (! empty($errors)) {
            $form = $this->frm->make('login', $data, $errors);
            return $this->index($form);
        }
        return $this->render('login');
    }

    public function register() : string
    {
        $registerForm = $this->frm->make('user/register');
        return $this->index($registerForm);
    }
}