<?php

declare(strict_types=1);

class FieldRenderer
{
    /** @var FieldHandlerInterface[] */
    private array $fieldHandlers;

    private ?InputLayoutInterface $defaultInputLayout = null;
    private array $namedLayouts = [];

    public function setFieldHandlers(array $fieldHandlers): self
    {
        $this->fieldHandlers = $fieldHandlers;
        return $this;
    }

    public function registerNamedLayout(string $name, InputLayoutInterface $layout): self
    {
        $this->namedLayouts[$name] = $layout;
        return $this;
    }

    public function render(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldType = $field['type'] ?? 'text';
        $inputElement = $this->createInputElement($field, $form, $formInstance);

        // Hidden fields don't need wrapping
        if ($fieldType === 'hidden') {
            return $inputElement;
        }

        // Apply icon decorations
        if ($formInstance->hasIconDecorations($field)) {
            $inputElement = $formInstance->wrapWithIcons($field, $inputElement, $form);
        }

        // Apply input layout
        $fieldId = $formInstance->getFieldId($field);
        $layout = $this->resolveInputLayout($field, $formInstance);

        return $layout->renderInput(
            $field,
            $inputElement,
            $fieldId,
            $form,
            $formInstance,
        );
    }

    public function setDefaultInputLayout(?InputLayoutInterface $defaultInputLayout): self
    {
        $this->defaultInputLayout = $defaultInputLayout;
        return $this;
    }

    public function resolveInputLayout(array $field, AbstractForm $formInstance): InputLayoutInterface
    {
        // PRIORITY 1: Field specifies a named layout
        if (isset($field['inputLayout']) && isset($this->namedLayouts[$field['inputLayout']])) {
            return $this->namedLayouts[$field['inputLayout']];
        }

        // PRIORITY 2: Form per-field named layout
        $formFieldLayoutName = $formInstance->getInputLayoutNameForField($field);
        if ($formFieldLayoutName !== null && isset($this->namedLayouts[$formFieldLayoutName])) {
            return $this->namedLayouts[$formFieldLayoutName];
        }

        // PRIORITY 3: Form default named layout
        $formDefaultLayoutName = $formInstance->getDefaultInputLayoutName();
        if ($formDefaultLayoutName !== null && isset($this->namedLayouts[$formDefaultLayoutName])) {
            return $this->namedLayouts[$formDefaultLayoutName];
        }

        // PRIORITY 4: System default layout
        if ($this->defaultInputLayout === null) {
            throw new RuntimeException('No input layout configured');
        }

        return $this->defaultInputLayout;
    }

    private function createInputElement(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldType = $field['type'] ?? 'text';

        foreach ($this->fieldHandlers as $handler) {
            if ($handler->supports($fieldType)) {
                return $handler->handle($field, $form, $formInstance);
            }
        }

        // Default fallback for unknown field types
        $fieldId = $formInstance->getFieldId($field);
        return $form->input($fieldType)
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '')
            ->class(InputBox::INPUT_CLASS);
    }
}