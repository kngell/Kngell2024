<?php

declare(strict_types=1);

class FieldRenderer
{
    /** @var FieldHandlerInterface[] */
    private array $fieldHandlers;

    public function __construct(
        private InputFieldHandler $inputFieldHandler,
        private TextareaFieldHandler $textareaFieldHandler,
        private SelectFieldHandler $selectFieldHandler,
        private DropzoneFieldHandler $dropzoneFieldHandler,
        private CurrencyFieldHandler $currencyFieldHandler,
        private FieldGroupFieldHandler $fieldGroupFieldHandler,
        private ButtonFieldHandler $buttonFieldHandler,
    ) {
        $this->fieldHandlers = [
            $inputFieldHandler,
            $textareaFieldHandler,
            $selectFieldHandler,
            $dropzoneFieldHandler,
            $currencyFieldHandler,
            $fieldGroupFieldHandler,
            $buttonFieldHandler,
        ];
    }

    public function render(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldType = $field['type'] ?? 'text';
        $inputElement = $this->createInputElement($field, $form, $formInstance);

        if ($formInstance->hasIconDecorations($field)) {
            $inputElement = $formInstance->wrapWithIcons($field, $inputElement, $form);
        }
        if ($fieldType !== 'hidden') {
            return $formInstance->wrapInInputBox($field, $inputElement, $form);
        }
        return $inputElement;
    }

    private function createInputElement(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldType = $field['type'] ?? 'text';

        foreach ($this->fieldHandlers as $handler) {
            if ($handler->supports($fieldType)) {
                return $handler->handle($field, $form, $formInstance);
            }
        }

        // Default to basic input
        $fieldId = $formInstance->getFieldId($field);
        return $form->input($fieldType)
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '')
            ->class(AbstractForm::INPUT_CLASS);
    }
}