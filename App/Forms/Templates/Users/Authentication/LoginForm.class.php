<?php

declare(strict_types=1);

readonly class LoginForm extends AbstractTemplateForm
{
    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $html = $this->builder;
        $formValues = $this->formValues($formValues);
        $form = $html->form()->formValues($formValues)->formErrors($formErrors);
        $form->action($action)->name('login-form')->id('login-form')->method('post')->add(
            $html->tag('div')->class(...self::INPUT_BOX_CLASS)->add(
                $form->input('email')->name('email')->id('email')->autofocus()->class(...self::INPUT_CLASS)->placeholder('Email')->required()->autocomplete('off'),
            ),
            $html->tag('div')->class(...self::INPUT_BOX_CLASS)->add(
                $form->input('password')->name('password')->id('login-password')->class(...self::INPUT_CLASS)->placeholder('Password'),
            ),
            $html->tag('div')->class(...self::CHECKBOX_CLASS)->add(
                $form->label('Remember Me')->for('remember')->class(...self::LABEL_CLASS)->id('input__label')->add(
                    $form->input('checkbox')->name('remember_me')->id('remember')->class(...self::INPUT_CHECKBOX_CLASS)
                )
            ),
            $html->tag('div')->class(...self::INPUT_BOX_CLASS)->add(
                $form->button()->type('submit')->class(...self::BUTTON_CLASS)->content('Log In')
            ),
            $html->tag('div')->class('text-center')->add(
                $html->tag('a')->href('/forgot')->class('forgot-password')->content('Forgot Password?')
            )
        );
        return $form->generate();
    }
}