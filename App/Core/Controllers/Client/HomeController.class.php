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

    // public function form() : string
    // {
    //     if ($this->session->exists('form')) {
    //         $form = $this->session->get(('form'));
    //         $this->session->delete('form');
    //     } else {
    //         $form = $this->frm->make('home/handleForm');
    //     }
    //     $flash = $this->flash->get();
    //     if (! empty($flash)) {
    //         $flash = ArrayUtils::first($flash);
    //         $message = '<h5>' . $flash['message'] . '</h5>';
    //         $form = $message . $form;
    //     }
    //     $this->pageTitle('Learning Forms');
    //     return $this->render('form', [
    //         'form' => $form,
    //     ]);
    // }

    public function handleForm() : Response
    {
        $data = $this->request->getPost()->getAll();
        $results = $this->validator->validate($data, 'login', $this->user);
        $form = $this->frm->make('home/handleForm', $data, $results);
        if (! $this->session->exists('form')) {
            $this->session->set('form', $form);
        }
        if ($this->token->validate($data['csrfToken'], $data['frm_name']) && empty($results)) {
            $this->flash->add('Form Submitted Sucessfully', FlashType::SUCCESS);
        }
        return $this->redirect('/home/form');
    }
}