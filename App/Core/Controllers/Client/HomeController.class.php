<?php

declare(strict_types=1);

class HomeController extends Controller
{
    public function __construct(private UsersModel $user, private FlashInterface $flash, private TestFormCreator $frm, private Validator $validator)
    {
    }

    public function index() : string
    {
        $this->pageTitle('Home');
        return $this->render('index', [
            'message' => $this->flash->get(),
        ]);
    }

    public function form() : string
    {
        $session = $this->token->getSession();
        if ($session->exists('form')) {
            $form = $session->get(('form'));
            $session->delete('form');
        } else {
            $form = $this->frm->make('home/handleForm');
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
        $results = $this->validator->validate($data, 'login', $this->user);
        $form = $this->frm->make('home/handleForm', $data, $results);
        $session = $this->token->getSession();
        if (! $session->exists('form')) {
            $session->set('form', $form);
        }
        if ($this->token->validate($data['csrfToken'], $data['frm_name']) && $this->token->isTokenTimeValid() && empty($results)) {
            $this->flash->add('Form Submitted Sucessfully', FlashType::SUCCESS);
        }
        return $this->redirect('/home/form');
    }
}