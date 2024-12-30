<?php

declare(strict_types=1);

readonly class SignupForm extends AbstractTemplateForm
{
    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $html = $this->builder;
        $formValues = $this->formValues($formValues);
        $form = $html->form()->formValues($formValues)->formErrors($formErrors);
        $form->action($action)->name('register-form')->id('register-form')->method('post')->role('form')->add(
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->label('First Name :')->for('first_name')->class(self::LABEL_CLASS),
                $form->input('text')->name('first_name')->id('first_name')->class(self::INPUT_CLASS)->placeholder('First Name')->autocomplete('off')->autofocus(true),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->label('Last Name :')->for('last_name')->class(self::LABEL_CLASS),
                $form->input('text')->name('last_name')->id('last_name')->class(self::INPUT_CLASS)->placeholder('Last Name')->autocomplete('off'),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->label('Username :')->for('username')->class(self::LABEL_CLASS),
                $form->input('text')->name('username')->id('username')->class(self::INPUT_CLASS)->placeholder('Username')->autocomplete('off'),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->label('Email :')->for('register_email')->class(self::LABEL_CLASS),
                $form->input('email')->name('email')->id('register_email')->class(self::INPUT_CLASS)->placeholder('Email Address')->autocomplete('off')->required(),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->label('Password :')->for('password')->class(self::LABEL_CLASS),
                $form->input('password')->name('password')->id('password')->class(self::INPUT_CLASS)->placeholder('Password')->autocomplete('off'),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->label('Confirm Password :')->for('confirm_password')->class(self::LABEL_CLASS),
                $form->input('password')->name('confirm_password')->id('confirm_password')->class(self::INPUT_CLASS)->placeholder('Confirm Password')->autocomplete('off'),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->button()->content('Sign Up')->class(['form-control', 'btn', 'btn-secondary'])
            ),
        );
        return $form->generate();
    }
}