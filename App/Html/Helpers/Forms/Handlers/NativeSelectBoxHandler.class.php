<?php

declare(strict_types=1);

class NativeSelectBoxHandler extends AbstractSelectHandler
{
    #[Override]
    protected function getSelectClasses(array $field): string
    {
        return InputBox::INPUT_SELECT;
    }

    #[Override]
    protected function isOptionDisabled(string|int $optionValue, array $field): bool
    {
        return $optionValue === '' && !empty($field['disabled']);
    }

    #[Override]
    protected function decorateSelect(
        AbstractHtmlComponent $select,
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
    ): AbstractHtmlComponent {
        if (empty($field['suffixIcon'])) {
            return $select;
        }

        $container = $form->tag('div')->class(InputBox::INPUT_CONTAINER);
        $container->add($select);
        $container->add(
            $formInstance->createIconWrapper(
                $field['suffixIcon'],
                InputBox::SUFFIX_CLASS,
                'Suffix',
                $form,
            ),
        );

        return $container;
    }
}