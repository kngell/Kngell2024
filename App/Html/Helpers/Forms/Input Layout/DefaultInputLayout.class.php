<?php

declare(strict_types=1);

class DefaultInputLayout implements InputLayoutInterface
{
    public function renderInput(
        array $field,
        AbstractHtmlComponent $inputElement,
        string $fieldId,
        FormBuilder $form,
    ): AbstractHtmlComponent {
        // Handle buttons and dropzone separately
        if (in_array($field['type'] ?? '', ['button', 'dropzone'])) {
            return isset($field['wrapper-class'])
                ? $form->tag('div')->class($field['wrapper-class'])->add($inputElement)
                : $inputElement;
        }

        $labelText = $field['label'] ?? ucfirst($field['name'] ?? '');
        $extraClass = $this->getFieldExtraClass($field);

        return $form->tag('div')
            ->class(InputBox::INPUT_BOX . $extraClass)
            ->add(
                $inputElement,
                $form->label($labelText)
                    ->for($fieldId)
                    ->class(InputBox::LABEL_CLASS),
            );
    }

    private function getFieldExtraClass(array $field): string
    {
        if (isset($field['class'])) {
            return is_array($field['class']) ? ' ' . implode(' ', $field['class']) : ' ' . $field['class'];
        }
        return '';
    }
}