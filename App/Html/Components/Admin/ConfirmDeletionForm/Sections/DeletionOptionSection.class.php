<?php

declare(strict_types=1);

final class DeletionOptionSection extends AbstractBaseHtmlSection
{
    public function getKey(): string
    {
        return ConfirmDeletionSection::OPTIONS->value;
    }

    public function getConfig(array $formValues = []): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $selectedValue = $formValues['delete_option'] ?? 'archive';

        return $html->tag('div')->class('deletion-options')->add(
            $html->tag('h4')->class('title')->content('Deletion Options'),
            $html->tag('div')->class('options')->add(
                // Archive option
                $html->tag('div')
                    ->class('options-box', $selectedValue === 'archive' ? 'selected' : '')
                    ->custom(['data-option' => 'archive', 'role' => 'button', 'tabindex' => '0'])
                    ->add(
                        $html->input('radio')
                            ->name('delete_option')
                            ->id('delete-option-archive')
                            ->value('archive')
                            ->style(['display' => 'none'])
                            ->checked($selectedValue === 'archive'),
                        $html->tag('span')->class('options-box__title')->content('Archive Product'),
                        $html->tag('span')->class('options-box__description')->content('Hide from storefront; data remains restorable.'),
                    ),

                // Permanent deletion option
                $html->tag('div')
                    ->class('options-box', $selectedValue === 'permanent' ? 'selected' : '')
                    ->custom(['data-option' => 'permanent', 'role' => 'button', 'tabindex' => '0'])
                    ->add(
                        $html->input('radio')
                            ->name('delete_option')
                            ->id('delete-option-permanent')
                            ->value('permanent')
                            ->style(['display' => 'none'])
                            ->checked($selectedValue === 'permanent'),
                        $html->tag('span')->class('options-box__title')->content('Delete Permanently'),
                        $html->tag('span')->class('options-box__description')->content('Remove product entirely from the system'),
                    ),
            ),
        );
    }
}