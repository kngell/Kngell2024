<?php

declare(strict_types=1);

final class DeletionCheckBoxSection extends AbstractBaseHtmlSection
{
    public function __construct(HtmlBuilder $htmlBuilder, IconBuilder $iconBuilder)
    {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function getKey(): string
    {
        return 'confirm_deletion_checkbox';
    }

    public function getConfig(array $formValues = []): array
    {
        return [
            [
                'key' => 'confirmDelete',
                'name' => 'confirm_delete',
                'label' => 'I understand this product will be hidden from customers',
                'class' => 'span-all',
                'type' => 'checkbox',
                'required' => true,
            ],
        ];
    }
}