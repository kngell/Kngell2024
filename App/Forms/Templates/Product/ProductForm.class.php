<?php

declare(strict_types=1);

final readonly class ProductForm implements FormTemplateInterface
{
    public function __construct(private FormBuilder $formBuilder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []) : string
    {
        $form = $this->formBuilder->form()->formValues($formValues)->formErrors($formErrors);
        return $form->name('new-product')->method('post')->class(['mb-3'])->action($action)->enctype(true)->add(
            ! empty($formValues) ? $form->input('hidden')->name('id')->value('') : null,
            $form->label()->for('pdt')->content('Name :')->class(['form-label']),
            $form->input('text')->name('name')->value('')->id('pdt')->class(['form-control']),
            $form->label()->for('description')->content('Description :')->class(['form-label']),
            $form->textArea()->name('description')->id('description')->class(['form-control']),
            $form->button()->content('save')->type(ButtontypeAttr::SUBMIT->value)->class(['button', 'btn',
                'btn-primary'])->name('button')
        )->makeForm();
    }
}