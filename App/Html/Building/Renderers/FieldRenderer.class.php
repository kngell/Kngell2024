<?php

declare(strict_types=1);

class FieldRenderer
{
    /** @var FieldHandlerInterface[] */
    private array $fieldHandlers;

    private ?InputLayoutInterface $defaultInputLayout = null;
    private array $namedLayouts = [];

    public function __construct(array $fieldHandlers = [])
    {
        $this->fieldHandlers = $fieldHandlers;
    }

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

    /**
     * Check if a named layout has been registered.
     */
    public function hasLayout(string $name): bool
    {
        return isset($this->namedLayouts[$name]);
    }

    /**
     * Get a named layout by name.
     */
    public function getLayout(string $name): ?InputLayoutInterface
    {
        return $this->namedLayouts[$name] ?? null;
    }

    /**
     * Get all registered layout names.
     */
    public function getLayoutNames(): array
    {
        return array_keys($this->namedLayouts);
    }

    public function render(array $field, FormBuilder $form, AbstractForm $formInstance, FormConfig|PageConfig $config): AbstractHtmlComponent
    {
        $fieldType = $field['type'] ?? 'text';
        $inputElement = $this->createInputElement($field, $form, $formInstance, $config);

        // Hidden fields don't need wrapping
        if ($fieldType === 'hidden') {
            return $inputElement;
        }

        // Apply icon decorations
        if ($formInstance->hasIconDecorations($field)) {
            $inputElement = $formInstance->wrapWithIcons($field, $inputElement, $form);
        }

        // Apply input layout
        $fieldId = $config->getFieldId($field);
        $layout = $this->resolveInputLayout($field, $formInstance, $config);

        return $layout->renderInput(
            $field,
            $inputElement,
            $fieldId,
            $form,
            $formInstance,
        );
    }

    public function setInputBoxLayout(?InputLayoutInterface $defaultInputLayout): self
    {
        $this->defaultInputLayout = $defaultInputLayout;
        return $this;
    }

    public function resolveInputLayout(array $field, AbstractForm $formInstance, FormConfig|PageConfig $config): InputLayoutInterface
    {
        // PRIORITY 1: Field specifies a named layout
        if (isset($field['inputLayout']) && $this->hasLayout($field['inputLayout'])) {
            return $this->getLayout($field['inputLayout']);
        }

        // PRIORITY 2: Form per-field named layout
        $formFieldLayoutName = $config->getInputLayoutNameForField($field);
        if ($formFieldLayoutName !== null && $this->hasLayout($formFieldLayoutName)) {
            return $this->getLayout($formFieldLayoutName);
        }

        // PRIORITY 3: Form default named layout
        $formDefaultLayoutName = $formInstance->getDefaultInputLayoutName();
        if ($formDefaultLayoutName !== null && $this->hasLayout($formDefaultLayoutName)) {
            return $this->getLayout($formDefaultLayoutName);
        }

        // PRIORITY 4: System default layout
        if ($this->defaultInputLayout === null) {
            throw new RuntimeException('No input layout configured');
        }

        return $this->defaultInputLayout;
    }

    private function createInputElement(array $field, FormBuilder $form, AbstractForm $formInstance, FormConfig|PageConfig $config): AbstractHtmlComponent
    {
        $fieldType = $field['type'] ?? 'text';

        foreach ($this->fieldHandlers as $handler) {
            if ($handler->supports($fieldType)) {
                return $handler->handle($field, $form, $formInstance, $config);
            }
        }

        // Default fallback for unknown field types
        $fieldId = $config->getFieldId($field);
        $input = $form->input($fieldType)
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '')
            ->class(InputBox::INPUT_CLASS);
        if (!empty($field['value'])) {
            $input->value($field['value']);
        }
        if (!empty($field['default'])) {
            $input->value($field['default']);
        }

        return $input;
    }
}