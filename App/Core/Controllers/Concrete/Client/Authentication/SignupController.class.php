<?php

declare(strict_types=1);

class SignupController extends Controller
{
    use ValidationTrait;

    public function __construct(private UserModel $user, private UserFormCreator $frm, private ValidatorInterface $validator, private HashInterface $hash, private ImagesUpload $imgUpload)
    {
        $this->currentModel($user);
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

        // Process image upload
        $this->imgUpload->proceed(false);
        $imageErrors = $this->imgUpload->getErrors();

        // Validate form data with image upload errors
        $validationResult = $this->validateFormData($data, 'register', $this->user, $imageErrors);

        // Check for validation errors and redirect if any
        if ($validationResult->hasErrors() || ! empty($imageErrors)) {
            return $this->redirect(DS . 'signup');
        }

        // Use validated data for security
        $validatedData = $validationResult->getValidatedData();
        $validatedData['password'] = $this->hash->password($validatedData['password']);

        // Add image paths if available
        if ($this->imgUpload->getMediaPaths() !== null) {
            $validatedData['media'] = $this->imgUpload->getMediaPaths();
        }

        // Save user
        $result = $this->user->saveRegisteredUser($validatedData, $this->token);
        if ($result->getQueryResult() && $result->getLastInsertId()) {
            $logIn = "<a href='/login'> Log In</a>";
            $this->flash->add('Congratulations!!! You have been registered successfully. You can now ' . $logIn);
            // $res = $this->notifyEmail($validatedData);
            return $this->redirect(DS . 'signup' . DS . 'success-singup');
        }

        $this->flash->add('An error occurred when saving data. Please try again', FlashType::DANGER);
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
        $url = HOST . '/signup/activate/' . $this->token->getValue();
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