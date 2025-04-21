<?php

declare(strict_types=1);

final readonly class DeletePostForm extends AbstractPostForm
{
    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []) : string
    {
        $formValues = $this->formValues($formValues);
        $form = $this->builder->form()->formValues($formValues)->formErrors($formErrors);
        $form->action($action)->method('post')->add(
            $form->tag('p')->content('Are you sure that you\'d like to delete this post?'),
            $form->button()->content('Yes')->class(['btn', 'btn-secondary'])
        );
        return $form->generate();
    }
}