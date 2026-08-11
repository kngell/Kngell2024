<?php

declare(strict_types=1);

class FieldCheckboxLayout extends AbstractFieldLayout
{
    public function renderInput(array $field, AbstractHtmlComponent $inputElement, string $fieldId, FormBuilder $form, ?AbstractForm $formInstance = null): AbstractHtmlComponent
    {
        // Determine if field has a value
        $hasValue = $this->hasValue($field);

        // Build wrapper classes
        $wrapperClasses = $this->buildWrapperClasses($field, $hasValue);

        // Build wrapper
        $wrapper = $form->tag('div')->class(...$wrapperClasses);

        // Build body
        $label = $form->label()->class('input-field__checkbox', 'input-field__checkbox--single')->add(
            $inputElement,
            $form->tag('span')->class('input-field__checkbox-custom'),
            $form->tag('span')->class('input-field__checkbox-label')->content($field['label'] ?? ''),
        );
        $wrapper->add($label);

        // Build footer
        $footer = $this->buildFooter($field, $form);
        if ($footer) {
            $wrapper->add($footer);
        }

        return $wrapper;
    }
}