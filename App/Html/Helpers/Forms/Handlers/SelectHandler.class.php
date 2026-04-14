<?php

declare(strict_types=1);

class SelectHandler extends AbstractSelectHandler
{
    // Uses default supports() - ['select', 'input-with-currency']

    protected function getSelectClasses(array $field): string
    {
        return InputBox::INPUT_SELECT;
    }

    protected function isOptionDisabled($optionValue, array $field): bool
    {
        return $optionValue === '' && ($field['disabled'] ?? false);
    }

    protected function decorateSelect($select, array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        if (!empty($field['suffixIcon'])) {
            $container = $form->tag('div')->class(InputBox::INPUT_CONTAINER);
            $container->add($select);
            $container->add(
                $formInstance->createIconWrapper($field['suffixIcon'], InputBox::SUFFIX_CLASS, 'Suffix', $form),
            );
            return $container;
        }
        return $select;
    }
}