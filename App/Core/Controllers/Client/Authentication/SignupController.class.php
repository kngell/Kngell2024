<?php

declare(strict_types=1);

class SignupController extends Controller
{
    public function __construct(private UsersModel $user, private UserFormCreator $frm, private Validator $validator, private HashInterface $hash)
    {
    }

    public function index() : string
    {
        $this->pageTitle('Register');
        if ($this->session->exists('form')) {
            $form = $this->session->get('form');
            $this->session->delete('form');
        } else {
            $form = $this->frm->make('register');
        }
        return $this->render('index', ['authForm' => $form, 'message' => $this->flash->get()]);
    }

    public function register() : Response
    {
        $data = $this->request->getPost()->getAll();
        $errors = $this->validator->validate($data, 'register', $this->user);
        $form = $this->frm->make('register', $data, $errors);
        if (! $this->session->exists('form')) {
            $this->session->set('form', $form);
        }
        if (! empty($errors)) {
            $this->flash->add('Fields Errors... Please check.', FlashType::WARNING);
            return $this->redirect(DS . 'signup');
        }
        $data['password'] = $this->hash->password($data['password']);
        $result = $this->user->save($data);
        if ($result->getQueryResult() && $result->getLastInsertId()) {
            $logIn = "<a href='/login'> Log In</a>";
            $this->flash->add('Congratulations!!! You have been registerd successfully. you can now ' . $logIn);
            return $this->redirect(DS . 'signup');
        }
        $this->flash->add('An error occures when saving data. Please try again', FlashType::DANGER);
        return $this->redirect(DS . 'signup');
    }
}