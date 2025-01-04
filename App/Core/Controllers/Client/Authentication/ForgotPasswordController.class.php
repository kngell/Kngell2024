<?php

declare(strict_types=1);

class ForgotPasswordController extends Controller
{
    public function __construct(
        private UserModel $user,
        private UserFormCreator $frm,
        private ValidatorInterface $validator,
        private HashInterface $hash
    ) {
    }

    public function index() : string
    {
        $this->pageTitle('Forgot Password');
        $form = $this->form($this->frm, 'forgot-pw');
        return $this->render('index', [
            'forgotForm' => $form,
        ]);
    }

    public function processForgotPassword() : Response
    {
        $userData = $this->request->getPost()->getAll();
        $errors = $this->validator->validate($userData, 'forgot-pw');
        $this->session->set('form', $this->frm->make('forgot-pw', $userData, $errors));
        if (empty($errors)) {
            $this->flash->add('Please check your Email');
        }
        $ok = $this->user->processPasswordResetRequest($this->token, $userData);
        if ($ok) {
            $result = $this->sendEmail($ok);
        }
        return $this->redirect('/forgot-password/check-email');
    }

    public function checkEmail() : string
    {
        $this->pageTitle('Email Check');
        return $this->render('email_check_msg');
    }

    public function reset(string $token) : Response|string
    {
        $this->pageTitle('Reset Password');
        $user = $this->user->getUserByResetPw($token);
        if ($user) {
            $form = $this->form($this->frm, 'reset-pw', ['token' => $token]);
            return $this->render('reset_pw', ['resetPwForm' => $form]);
        }
        return  $this->render('token_expires');
    }

    public function resetPassword() : string|Response
    {
        $data = $this->request->getPost()->getAll();
        $errors = $this->validator->validate($data, 'reset-pw');
        $form = $this->form($this->frm, 'reset-pw', $data, $errors);
        if (! empty($errors)) {
            $this->session->set('form', $form);
            return $this->redirect('/password/reset/' . $data['token']);
        }
        $user = $this->user->getUserByResetPw($data['token']);
        if ($user && $this->user->resetPassword($user, $data, $this->hash)) {
            return $this->redirect('/forgot-password/reset-success');
        }
        return   $this->render('token_expires');
    }

    public function resetSuccess() : string
    {
        $this->pageTitle('Reset Password');
        return $this->render('reset_success');
    }

    private function sendEmail(array $params) : ?object
    {
        $this->setLayout('email');
        $host = $this->request->getServer()->get('http_host');
        $url = 'https://' . $host . '/password/reset/' . $params['token'];
        $html = $this->render('reset_email', ['url' => $url]);
        return $this->eventManager->notify(new ResetPwEvent(
            [
                'email' => $params['user']->getEmail(),
                'html' => $html,
                'subject' => 'Password reset',
            ]
        ), $this);
    }
}
