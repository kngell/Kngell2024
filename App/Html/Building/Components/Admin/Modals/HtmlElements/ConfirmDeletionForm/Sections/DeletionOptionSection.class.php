<?php

declare(strict_types=1);

final class DeletionOptionSection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        HtmlEscaper $escaper,
        private FormOptions $optionBuilder,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder, $escaper);
    }

    public function getKey(): string
    {
        return ConfirmDeletionSection::OPTIONS->value;
    }

    public function getConfig(array $formValues = []): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $label = $formValues['label'] ?? 'Item';

        $options = $this->optionBuilder
            ->setInputName('delete_option')
            ->setDefaultOption('archive')
            ->addOption(
                value: 'archive',
                title: 'Archive ' . $label,
                description: 'Hide from site; data remains restorable.',
            )
            ->addOption(
                value: 'permanent',
                title: 'Delete Permanently',
                description: 'Remove '
                    . strtolower($label)
                    . ' entirely from the system.',
            )
            ->build();

        return $html->tag('div')->class('deletion-options')->add(
            $html->tag('h4')->class('title')->content('Deletion Options'),
            $options,
        );
    }
}