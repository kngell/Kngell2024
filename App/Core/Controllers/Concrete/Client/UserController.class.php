<?php

declare(strict_types=1);

class UserController extends Controller
{
    public function __construct(private UserModel $user, private UserFormCreator $frm, private ValidatorInterface $validator)
    {
        $this->currentModel($this->user);
    }

    public function edit(int $id) : string
    {
        $this->pageTitle('Edit User');
        return $this->render('edit');
    }
}