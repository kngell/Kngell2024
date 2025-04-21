<?php

declare(strict_types=1);
readonly class ForgotPwForm extends AbstractTemplateForm
{
    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $html = $this->builder;
        $formValues = $this->formValues($formValues);
        $form = $html->form()->formValues($formValues)->formErrors($formErrors);
        $form->method('post')->action($action)->add(
            $form->tag('div')->class(self::INPUT_BOX_CLASS)->add(
                $form->label('Email Address:')->for('input-email')->class(self::LABEL_CLASS),
                $form->input('email')->id('input-email')->class(self::INPUT_CLASS)->name('email')->placeholder('Email Address')->autofocus()->required()
            ),
            $form->button('submit')->content('Send Password reset')->class(self::BUTTON_CLASS),
        );
        return $form->generate();
    }
}