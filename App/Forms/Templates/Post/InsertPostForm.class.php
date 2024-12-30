<?php

declare(strict_types=1);

final readonly class InsertPostForm extends AbstractPostForm
{
    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []) : string
    {
        $formValues = $this->formValues($formValues);
        $form = $this->builder->form()->formValues($formValues)->formErrors($formErrors);
        $form->action($action)->method('post')->add(
            ...$this->formElements($form)
        );
        return $form->generate();
    }
}