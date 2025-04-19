<?php

declare(strict_types=1);

class SignupController extends Controller
{
    public function __construct(private UserModel $user, private UserFormCreator $frm, private ValidatorInterface $validator, private HashInterface $hash, private ImagesUpload $imgUpload)
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
        return $this->render('index', ['authForm' => $form]);
    }

    public function successSingup() : string
    {
        $this->pageTitle('Register');
        return $this->render('success');
    }

    public function register() : Response
    {
        $data = $this->request->getPost()->getAll();
        $errors = $this->validator->validate($data, 'register', $this->user);
        [$imgErrors,$imgPath] = $this->imgUpload->proceed(false);
        $errors = array_merge($errors, $imgErrors);
        $form = $this->frm->make('register', $data, $errors);
        if (! $this->session->exists('form')) {
            $this->session->set('form', $form);
        }
        if (! empty($errors)) {
            $this->flash->add('Fields Errors... Please check.', FlashType::WARNING);
            return $this->redirect(DS . 'signup');
        }
        $data['password'] = $this->hash->password($data['password']);
        $data['media'] = $imgPath;
        $result = $this->user->saveRegisteredUser($data, $this->token);
        if ($result->getQueryResult() && $result->getLastInsertId()) {
            $logIn = "<a href='/login'> Log In</a>";
            $this->flash->add('Congratulations!!! You have been registerd successfully. you can now ' . $logIn);
            // $res = $this->notifyEmail($data);
            return $this->redirect(DS . 'signup' . DS . 'success-singup');
        }
        $this->flash->add('An error occures when saving data. Please try again', FlashType::DANGER);
        return $this->redirect(DS . 'signup');
    }

    public function activate(string $token) : Response
    {
        $result = $this->user->activateAccount($token);
        if ($result) {
            return $this->redirect('/signup/account-activated');
        }
        return $this->redirect('');
    }

    public function accountActivated() : string
    {
        $this->pageTitle('Account activated');
        return $this->render('activated');
    }

    private function notifyEmail(array $params) : ?object
    {
        $this->setLayout('email');
        $host = $this->request->getServer()->get('http_host');
        $url = 'https://' . $host . '/signup/activate/' . $this->token->getValue();
        $html = $this->render('activation_email', ['url' => $url]);
        return $this->eventManager->notify(new RegisterEvent(
            [
                'email' => $params['email'],
                'html' => $html,
                'subject' => 'Account activation',
            ]
        ), $this);
    }
}