<?php

declare(strict_types=1);

readonly class ResetPwForm extends AbstractTemplateForm
{
    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $html = $this->builder;
        $formValues = $this->formValues($formValues);
        $form = $html->form()->formValues($formValues)->formErrors($formErrors);
        $form->action($action)->name('reset-pw-form')->id('reset-pw-form')->method('post')->role('form')->add(
            $form->input('hidden')->name('token'),
            $html->tag('div')->class(...self::INPUT_BOX_CLASS)->add(
                $form->label('Password :')->for('password')->class(...self::LABEL_CLASS),
                $form->input('password')->name('password')->id('password')->class(...self::INPUT_CLASS)->placeholder('Password')->autocomplete('off'),
            ),
            $html->tag('div')->class(...self::INPUT_BOX_CLASS)->add(
                $form->label('Confirm Password :')->for('confirm_password')->class(...self::LABEL_CLASS),
                $form->input('password')->name('confirm_password')->id('confirm_password')->class(...self::INPUT_CLASS)->placeholder('Confirm Password')->autocomplete('off'),
            ),
            $html->tag('div')->class(...self::INPUT_BOX_CLASS)->add(
                $form->button()->content('Reset Password')->class(...self::BUTTON_CLASS)
            ),
        );
        return $form->generate();
    }
}