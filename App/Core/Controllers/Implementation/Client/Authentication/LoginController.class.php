<?php

declare(strict_types=1);

class LoginController extends AuthController
{
    use ValidationTrait;

    public function __construct(
        UserModel $users,
        UserSessionModel $userSession,
        private UserFormCreator $frm,
        private ValidatorInterface $validator,
        private HtmlBuilder $html,
        HashInterface $hash,
        LoginAttemptsModel $loginAttempts
    ) {
        parent::__construct($users, $userSession, $hash, $loginAttempts);
    }

    public function before() : void
    {
        echo 'this is before';
    }

    public function index() : string
    {
        $this->pageTitle('Login');
        if ($this->session->exists('form') && ! empty($this->session->get('form'))) {
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
        $userData = $this->request->getPost()->getAll();

        // Validate user data
        $validationResult = $this->validateFormData($userData, 'login', $this->user);

        // Check for validation errors
        if ($validationResult->hasErrors()) {
            return new RedirectResponse('/login');
        }

        // Use validated data for authentication
        $validatedData = $validationResult->getValidatedData();

        if (! $this->isUserAuthenticated($validatedData)) {
            return new RedirectResponse('/login');
        }

        $eventResult = $this->eventManager->notify(LoginEvent::class, $this);
        return new RedirectResponse($this->getRedirectUrl() ?? '/');
    }
}