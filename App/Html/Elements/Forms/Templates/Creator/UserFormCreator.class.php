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
            StringUtils::strContainsArrayItem($action, ['forgot-pw']) => new ForgotPwForm($this->builder),
            StringUtils::strContainsArrayItem($action, ['reset-pw']) => new ResetPwForm($this->builder),
            StringUtils::strContainsArrayItem($action, ['save-profile']) => new ProfileForm($this->builder),
            default => null,
        };
    }
}