<?php

declare(strict_types=1);

class SelectFieldHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return in_array($fieldType, ['select', 'input-with-currency']);
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);
        $select = $form->select()
            ->id($fieldId)
            ->class(AbstractForm::INPUT_SELECT)
            ->name($field['name']);

        foreach ($field['options'] as $value => $label) {
            $isDisabled = $value === '';
            $select->add(
                $form->option((string) $value, $label)
                    ->disabled($isDisabled)
                    ->selected($isDisabled),
            );
        }

        // Handle icon decorations
        if (!empty($field['suffixIcon'])) {
            $container = $form->tag('div')->class(AbstractForm::INPUT_CONTAINER);
            $container->add($select);
            $container->add(
                $formInstance->createIconWrapper($field['suffixIcon'], AbstractForm::SUFFIX_CLASS, 'Suffix', $form),
            );
            return $container;
        }

        return $select;
    }
}