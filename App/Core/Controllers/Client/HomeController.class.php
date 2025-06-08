<?php

declare(strict_types=1);
class HomeController extends Controller
{
    public function __construct(private UserModel $user, private TestFormCreator $frm, private Validator $validator)
    {
        $this->currentModel($user);
    }

    public function index() : string
    {
        $this->pageTitle('Home');
        return $this->render('index');
    }

    // public function handleForm() : Response
    // {
    //     $userData = $this->request->getPost()->getAll();

    //     // Validate user data
    //     $validationResult = $this->validateFormData($userData, 'login', $this->user);

    //     // Check for validation errors
    //     if ($validationResult->hasErrors()) {
    //         return new RedirectResponse('/');
    //     }

    //     // Use validated data for authentication
    //     $validatedData = $validationResult->getValidatedData();

    //     $form = $this->frm->make('home/handleForm', $userData, $validationResult);
    //     if (! $this->session->exists('form')) {
    //         $this->session->set('form', $form);
    //     }
    //     if ($this->token->validate($userData['csrfToken'], $userData['frm_name']) && empty($results)) {
    //         $this->flash->add('Form Submitted Sucessfully', FlashType::SUCCESS);
    //     }
    //     return $this->redirect('/home/form');
    // }
}