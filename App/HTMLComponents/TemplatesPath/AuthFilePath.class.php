<?php

declare(strict_types=1);

class AuthFilePath implements TemplatePathsInterface
{
    private string $modalPath = VIEW . 'Frontend' . DS . 'components' . DS . 'Authentication' . DS;
    private string $templatePath = APP . 'HTMLComponents' . DS . 'Components' . DS . 'AuthSystem' . DS . 'Templates' . DS;

    public function Paths() : CollectionInterface
    {
        return new Collection(array_merge($this->authTemplates(), $this->authModals()));
    }

    private function authTemplates() : array
    {
        return [
            'authTemplatePath' => $this->templatePath . 'authTemplate.php',
            'loginTemplatePath' => $this->templatePath . 'LoginFormTemplate.php',
            'registerTemplatePath' => $this->templatePath . 'RegisterFormTemplate.php',
            'forgotPwTemplatePath' => $this->templatePath . 'ForgotPasswordFormTemplate.php',
            'verifyAccounTemplatePath' => $this->templatePath . 'VerifyUserAccountFormTemplate.php',
        ];
    }

    private function authModals() : array
    {
        return [
            'loginboxPath' => $this->modalPath . 'loginModal.php',
            'registerboxPath' => $this->modalPath . 'registerModal.php',
            'forgotboxPath' => $this->modalPath . 'forgorPwModal.php',
        ];
    }
}