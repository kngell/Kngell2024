<?php

declare(strict_types=1);

class ProductFormConfirmation implements FormTemplateInterface
{
    public function __construct(private HtmlBuilder $formBuilder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $form = $this->formBuilder;
        return $form->form()->name('confirm-delete-product')->method('post')->action($action)->add(
            $form->htmlTag('p')->class(['text-center'])->content('Are you sur you want to delete this product?'),
            $form->button()->name('submit')->content('Yes')->class('btn', 'btn-info'),
        )->makeForm();
    }
}