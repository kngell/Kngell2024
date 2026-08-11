<?php

declare(strict_types=1);

final readonly class TestForm implements FormTemplateInterface
{
    public function __construct(private FormBuilder $formBuilder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $form = $this->formBuilder->form()->formValues($formValues)->formErrors($formErrors);
        return $form->method('post')->class(['w-25'])->action($action)->formValues($formValues)->formErrors($formErrors)->add(
            $form->wrapper('div')->class(['mb-3', 'input-box'])->id('input-box1')->add(
                $form->label('Name :'),
                $form->input('text')->name('name')->class(['form-control'])
            ),
            $form->wrapper('div')->class(['mb-3', 'input-box'])->add(
                $form->label()->content('Email :'),
                $form->input('text')->name('email')->class(['form-control']),
            ),
            $form->wrapper('div')->class(['mb-3', 'input-box'])->add(
                $form->label('Message :'),
                $form->textArea()->rows(4)->name('message')->class(['form-control'])
            ),
            $form->button()->content('Send Message')->class(['btn', 'btn-primary', 'w-100'])
        )->makeForm();
    }
}