<?php

declare(strict_types=1);

class FieldLayout extends AbstractFieldLayout
{
    public function renderInput(
        array $field,
        AbstractHtmlComponent $inputElement,
        string $fieldId,
        FormBuilder $form,
        ?AbstractForm $formInstance = null,
    ): AbstractHtmlComponent {
        // Determine if field has a value
        $hasValue = $this->hasValue($field);

        // Build wrapper classes
        $wrapperClasses = $this->buildWrapperClasses($field, $hasValue);

        // Build wrapper
        $wrapper = $form->tag('div')->class(...$wrapperClasses);

        // Build body
        if ($this->needBody($field)) {
            $body = $this->buildBody($field, $inputElement, $fieldId, $form, $formInstance);
            $wrapper->add($body);
        } else {
            $wrapper->add($inputElement);
        }

        // Build footer
        $footer = $this->buildFooter($field, $form);
        if ($footer) {
            $wrapper->add($footer);
        }

        return $wrapper;
    }
}