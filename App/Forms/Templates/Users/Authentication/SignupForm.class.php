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
            $html->tag('div')->class(['input-container'])->add(
                $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                    $form->input('text')->name('first_name')->id('first_name')->class(self::INPUT_CLASS)->autocomplete('off')->autofocus(true),
                    $form->label('First Name :')->for('first_name')->class(self::LABEL_CLASS),
                ),
                $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                    $form->input('text')->name('last_name')->id('last_name')->class(self::INPUT_CLASS)->autocomplete('off'),
                    $form->label('Last Name :')->for('last_name')->class(self::LABEL_CLASS),
                ),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->input('text')->name('username')->id('username')->class(self::INPUT_CLASS)->autocomplete('off'),
                $form->label('Username :')->for('username')->class(self::LABEL_CLASS),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->input('email')->name('email')->id('register_email')->class(self::INPUT_CLASS)->autocomplete('off')->required(),
                $form->label('Email :')->for('register_email')->class(self::LABEL_CLASS),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->input('password')->name('password')->id('password')->class(self::INPUT_CLASS)->autocomplete('off'),
                $form->label('Password :')->for('password')->class(self::LABEL_CLASS),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->input('password')->name('confirm_password')->id('confirm_password')->class(self::INPUT_CLASS)->autocomplete('off'),
                $form->label('Confirm Password :')->for('confirm_password')->class(self::LABEL_CLASS),
            ),
            $html->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->button()->content('Sign Up')->class(self::BUTTON_CLASS)
            ),
        );
        return $form->generate();
    }
}