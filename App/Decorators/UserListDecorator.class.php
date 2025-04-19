<?php

declare(strict_types=1);

class UserListDecorator extends AbstractHtmlDecorator
{
    private const array USER_INFOS = ['firstName', 'lastName', 'email', 'username', 'phone', 'media', 'action'];

    private UserModel $userModel;

    public function __construct(Controller $controller)
    {
        parent::__construct($controller);
        $this->userModel = $this->userModel();
        $this->builder = $controller->getBuilder();
    }

    public function page(): array
    {
        $userData = $this->userModel->all()->getResults('class', 'User')->all();
        $userList = new UserListHtmlElement($userData, $this->builder, self::USER_INFOS);
        return array_merge(
            $this->controller->page(),
            ['userList' => $userList->display()]
        );
    }

    private function userModel(): UserModel
    {
        $userModel = $this->controller->getCurrentModel();
        if (! $userModel instanceof UserModel) {
            throw new InvalidArgumentException(sprintf(
                'Model %s is not a valid post model',
                get_class($userModel)
            ));
        }
        return $userModel;
    }
}