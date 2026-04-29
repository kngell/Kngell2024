<?php

declare(strict_types=1);

final class DeletionCheckBoxSection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function getKey(): string
    {
        return ConfirmDeletionSection::CHECKBOX->value;
    }

    public function getConfig(array $formValues = []): array
    {
        $label = $formValues['confirm_label']
            ?? 'I understand this item will be affected';

        return [
            [
                'key' => 'confirmDelete',
                'name' => 'confirm_delete',
                'label' => $label,
                'class' => 'span-all',
                'type' => 'checkbox',
                'required' => true,
                'footer' => [
                    'error' => '',
                ],
            ],
        ];
    }
}