<?php

declare(strict_types=1);
class ProfileController extends Controller
{
    public function __construct(private UserFormCreator $frm, private ValidatorInterface $validator, private UserModel $user, private HashInterface $hash)
    {
    }

    public function show() : string
    {
        $this->pageTitle('Profile');
        $userProfile = new UserProfileDecorator($this);
        return $this->render('show', $userProfile->page());
    }

    public function edit() : string
    {
        $this->pageTitle('Profile');
        if (! $this->session->exists('form')) {
            $user = AuthService::currentUser();
            $form = $this->frm->make('save-profile', $user);
        } else {
            $form = $this->session->get('form');
            $this->session->delete('form');
        }

        return $this->render('edit', ['ProfileForm' => $form]);
    }

    public function update() : Response
    {
        $this->pageTitle('Profile');
        $data = $this->request->getPost()->getAll();
        $data = $this->checkPassword($data);
        $errors = $this->validator->validate($data, 'profile', $this->user);
        $form = $this->form($this->frm, 'save-profile', $data, $errors);
        if ($errors) {
            $this->session->set('form', $form);
            return $this->redirect('/profile/edit');
        }
        $result = $this->user->updatUser($data, $this->hash);
        if ($result->getQueryResult() && $result->rowCount()) {
            $this->flash->add('Changes saved!');
            return $this->redirect('/profile/show');
        }
        $this->flash->add("An error occures. Please try again or <a href='/'>continue</a>", FlashType::WARNING);
        return $this->redirect('/profile/edit');
    }

    private function checkPassword(array $data) : array
    {
        $newData = [];
        foreach ($data as $input => $value) {
            if (! empty($value)) {
                $newData[$input] = $value;
            }
        }
        return $newData;
    }
}