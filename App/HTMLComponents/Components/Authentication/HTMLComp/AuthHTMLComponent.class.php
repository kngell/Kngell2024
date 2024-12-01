<?php

declare(strict_types=1);
class AuthHTMLComponent extends AbstractHTMLComponent
{
    private string $section = 'authenticationComponent';
    private string $loginBox = '';
    private string $registerBox = '';
    private string $forgotPwBox = '';

    public function __construct(array $params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        $childs = $this->children->all();
        foreach ($childs as $child) {
            [$property,$html] = $child->display();
            if (property_exists($this, $property)) {
                $this->{$property} = $html;
            }
        }
        return [$this->section => $this->authSystem()];
    }

    public function authSystem(): string
    {
        $authTemplate = $this->getTemplate('authTemplatePath');
        $authTemplate = str_replace('{{loginBox}}', $this->loginBox, $authTemplate);
        $authTemplate = str_replace('{{registerBox}}', $this->registerBox, $authTemplate);
        return str_replace('{{forgotBox}}', $this->forgotPwBox, $authTemplate);
    }
}