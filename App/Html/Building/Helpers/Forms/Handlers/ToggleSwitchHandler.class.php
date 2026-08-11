<?php

declare(strict_types=1);

class ToggleSwitchHandler extends AbstractBaseFieldHandler implements FieldHandlerInterface
{
    #[Override]
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'toggle-switch';
    }

    #[Override]
    public function handle(array $field, FormBuilder $form, ?AbstractForm $formInstance = null, null|FormConfig|PageConfig $config = null): AbstractHtmlComponent
    {
        // Use a label as the container for better accessibility
        $toggleContainer = $form->label()->class('input-field__toggle');

        if (($field['labelPosition'] ?? null) === 'left') {
            $toggleContainer->class('input-field__toggle--label-left');
        }

        // Input first (hidden but functional)
        if (empty($field['name'])) {
            throw new RuntimeException('Toggle Switch should have a name attribute');
        }

        $toggleContainer->add(
            $form->input('checkbox')
                ->name($field['name'])
                ->class('input-field__toggle-input'),
        );

        // Slider second (visual element)
        $toggleContainer->add(
            $form->tag('span')->class('input-field__toggle-slider'),
        );

        // Label text last
        if (!empty($field['label'])) {
            $label = $form->tag('span')
                ->class('input-field__toggle-label')
                ->content($field['label']);
            $toggleContainer->add($label);
        }

        return $toggleContainer;
    }
}