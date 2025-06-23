<?php

declare(strict_types=1);

class UserProfileDecorator extends AbstractHtmlDecorator
{
    public function __construct(Controller $controller)
    {
        parent::__construct($controller);
    }

    public function page(): array
    {
        $userProfile = new UserProfileHtmlElement($this->builder);
        return array_merge(
            ['userProfile' => $userProfile->display()],
            $this->controller->page()
        );
    }
}