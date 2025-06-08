<?php

declare(strict_types=1);

class ForgotPasswordController extends Controller
{
    use ValidationTrait;

    public function __construct(
        private UserModel $user,
        private UserFormCreator $frm,
        private ValidatorInterface $validator,
        private HashInterface $hash
    ) {
        $this->setLayout('email');
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

        // Validate forgot password data
        $validationResult = $this->validateFormData($userData, 'forgot-pw');

        if ($validationResult->isValid()) {
            $this->flash->add('Please check your Email');
            $validatedData = $validationResult->getValidatedData();
            $ok = $this->user->processPasswordResetRequest($this->token, $validatedData);
            if ($ok) {
                $result = $this->sendEmail($ok);
            }
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

        // Validate reset password data
        $validationResult = $this->validateFormData($data, 'reset-pw');

        if ($validationResult->hasErrors()) {
            return $this->redirect('/password/reset/' . $data['token']);
        }

        $validatedData = $validationResult->getValidatedData();
        $user = $this->user->getUserByResetPw($validatedData['token']);

        if ($user && $this->user->resetPassword($user, $validatedData, $this->hash)) {
            return $this->redirect('/forgot-password/reset-success');
        }

        return $this->render('token_expires');
    }

    public function resetSuccess() : string
    {
        $this->pageTitle('Reset Password');
        return $this->render('reset_success');
    }

    private function sendEmail(array $params) : ?object
    {
        $url = HOST . '/password/reset/' . $params['token'];
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