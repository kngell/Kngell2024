<?php

declare(strict_types=1);

class UserFormCreator extends AbstractFormCreator
{
    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function create(string $action): ?FormTemplateInterface
    {
        return match (true) {
            StringUtils::strContainsArrayItem($action, ['auth-user', 'login-from-cookie']) => new LoginForm($this->builder),
            StringUtils::strContainsArrayItem($action, ['register']) => new SignupForm($this->builder),
            default => null,
        };
    }
}