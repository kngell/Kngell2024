<?php

declare(strict_types=1);

readonly class LoginForm extends AbstractTemplateForm
{
    public function __construct(private FormBuilder $formBuilder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $formValues = $this->formValues($formValues);
        $form = $this->formBuilder->form()->formValues($formValues)->formErrors($formErrors);
        $form->action($action)->name('login-form')->id('login-form')->method('post')->add(
            $form->wrapper('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->input('text')->name('email')->id('email')->autofocus()->class(self::INPUT_CLASS)->placeholder('Email')->required(),
            ),
            $form->wrapper('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->input('password')->name('password')->id('login-password')->class(self::INPUT_CLASS)->placeholder('Password'),
            ),
            $form->wrapper('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->input('checkbox')->name('remember')->id('remember')->class(self::INPUT_CHECKBOX_CLASS),
                $form->label('Remember Me')->for('remember')->class(self::LABEL_CLASS)
            ),
            $form->wrapper('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->button()->type('submit')->class(['btn', 'btn-primary'])->content('Log In')
            ),
            $form->wrapper('div')->class(['text-center'])->add(
                $form->htmlTag('a')->href('/user/recover')->class(['forgot-password'])->content('Forgot Password?')
            )
        );
        return $form->makeForm();
    }
}
