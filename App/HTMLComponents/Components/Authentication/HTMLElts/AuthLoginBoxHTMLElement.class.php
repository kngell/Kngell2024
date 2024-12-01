<?php

declare(strict_types=1);
class AuthLoginBoxHTMLElement extends AbstractHTMLElement
{
    private string $loginLabel = '<div>&nbspRemember Me&nbsp</div>';
    private string $section = 'loginBox';
    private string $template;

    public function __construct(?array $params, TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
        // $this->template = $this->getTemplate('loginTemplatePath');
    }

    public function display(): array
    {
        $Box = $this->getTemplate('loginboxPath');
        $box = str_replace('{{loginForm}}', $this->loginForm(), $Box);
        return[$this->section, $box];
    }

    private function loginForm() : string
    {
        $loginTemplate = $this->getTemplate('loginTemplatePath');
        $frm = isset($this->params['frm']) ? $this->params['frm'] : '';
        $form = $frm->setTemplate($loginTemplate);
        $print = $form->getPrint();
        $form->form([
            'action' => '',
            'id' => 'login-frm',
            'class' => ['login-frm'],
            'enctype' => 'multipart/form-data',
        ]);
        $loginTemplate = str_replace('{{form_begin}}', $form->begin(), $loginTemplate);
        $loginTemplate = str_replace('{{email}}', $form->input($print->email(name:'email'))
            ->placeholder(' ')
            ->class(['email', 'input-box__input'])
            ->id('email')
            ->label('Email :')
            ->labelClass(['input-box__label'])
            ->html(), $loginTemplate);
        $loginTemplate = str_replace('{{password}}', $form->input($print->password(name:'password'))
            ->placeholder(' ')
            ->class(['input-box__input'])
            ->id('password')
            ->Label('Password :')
            ->labelClass(['input-box__label'])
            ->html(), $loginTemplate);
        $loginTemplate = str_replace('{{remamber_me}}', $form->input($print->checkbox(name:'remember'))
            ->labelClass(['checkbox'])
            ->label($this->loginLabel)
            ->spanClass(['checkbox__box text-danger'])
            ->id('remember_me')
            ->html(), $loginTemplate);
        $loginTemplate = str_replace('{{submit}}', $form->input($print->submit(name: 'sigin'))
            ->label('Login')->id('sigin')
            ->html(), $loginTemplate);
        return str_replace('{{form_end}}', $form->end(), $loginTemplate);
    }
}