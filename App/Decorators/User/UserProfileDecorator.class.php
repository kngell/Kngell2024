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
        $userProfile = App::diget(UserProfileHtmlElement::class);
        return array_merge(
            ['userProfile' => $userProfile->display()],
            $this->controller->page()
        );
    }
}