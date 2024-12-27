<?php

declare(strict_types=1);

class UserFormCreator extends AbstractFormCreator
{
    public function __construct(private FormBuilder $formBuilder)
    {
    }

    public function create(string $action): ?FormTemplateInterface
    {
        return match (true) {
            StringUtils::strContainsArrayItem($action, ['auth-user']) => new LoginForm($this->formBuilder),
            StringUtils::strContainsArrayItem($action, ['register']) => new SignupForm($this->formBuilder),
            default => null,
        };
    }
}
