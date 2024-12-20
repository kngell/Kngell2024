<?php

declare(strict_types=1);

class HomeController extends Controller
{
    private UsersModel $users;
    private FlashInterface $flash;

    public function __construct(UsersModel $users, FlashInterface $flash)
    {
        $this->users = $users;
        $this->flash = $flash;
    }

    public function index() : string
    {
        $this->pageTitle('Home');
        return $this->render('index');
    }

    public function form() : string
    {
        if ($this->session->exists('form')) {
            $form = $this->session->get(('form'));
            $this->session->delete('form');
        } else {
            $form = $this->makeForm('home/handleForm');
        }
        $flash = $this->flash->get();
        if (! empty($flash)) {
            $flash = ArrayUtils::first($flash);
            $message = '<h5>' . $flash['message'] . '</h5>';
            $form = $message . $form;
        }
        $this->pageTitle('Learning Forms');
        return $this->render('form', [
            'form' => $form,
        ]);
    }

    public function handleForm() : Response
    {
        $data = $this->request->getPost()->getAll();
        $validator = new Validator($data, 'login', $this->users);
        $results = $validator->validate();
        $form = $this->makeForm('home/handleForm', $data, $results);
        if (! $this->session->exists('form')) {
            $this->session->set('form', $form);
        }
        if ($this->token->validate($data['csrfToken'], $data['frm_name']) && $this->token->isTokenTimeValid() && empty($results)) {
            $this->flash->add('Form Submitted Sucessfully', FlashType::SUCCESS->value);
        }
        return $this->redirect('/home/form');
    }

    public function makeForm(string $action = '', array $formValues = [], array $formErrors = []) : string
    {
        $form = $this->formBuilder->form();
        return $form->method('post')->class(['w-25'])->action($action)->formValues($formValues)->formErrors($formErrors)->add(
            $form->wrapper('div')->class(['mb-3', 'input-box'])->id('input-box1')->add(
                $form->label('Name :'),
                $form->input('text')->name('name')->class(['form-control'])
            ),
            $form->wrapper('div')->class(['mb-3', 'input-box'])->add(
                $form->label()->content('Email :'),
                $form->input('text')->name('email')->class(['form-control']),
            ),
            $form->wrapper('div')->class(['mb-3', 'input-box'])->add(
                $form->label('Message :'),
                $form->textArea()->rows(4)->name('message')->class(['form-control'])
            ),
            $form->button()->content('Send Message')->class(['btn', 'btn-primary', 'w-100'])
        )->makeForm();
    }
}