<?php

declare(strict_types=1);

class InputFieldHandler extends AbstractBaseFieldHandler implements FieldHandlerInterface
{
    private const array INPUT_TYPES = ['text', 'number', 'email', 'password', 'tel', 'url', 'checkbox', 'radio', 'date'];

    public function supports(string $fieldType): bool
    {
        return in_array($fieldType, self::INPUT_TYPES, true);
    }

    public function handle(array $field, FormBuilder $form, ?AbstractForm $formInstance = null, null|FormConfig|PageConfig $config = null): AbstractHtmlComponent
    {
        $fieldId = $this->fieldId = $field['id'] ?? $config?->getFieldId($field) ?? 'field_id';
        $fieldType = $field['type'] ?? 'text';

        // Typage sémantique des classes CSS (BEM)
        $class = match ($fieldType) {
            'checkbox' => ['input-field__checkbox-input'],
            'radio' => ['input-field__radio-input'],
            default => ['input-field__input'],
        };

        $inputField = $form->input($fieldType)
            ->name($field['name'])
            ->class(...$class)
            ->id($fieldId)
            ->placeholder(' ');

        // Gestion de la valeur et des états spécifiques
        if (isset($field['value'])) {
            $inputField->value($field['value']);
        }

        // Attributs booléens : validation stricte
        if (!empty($field['required'])) {
            $inputField->required($field['required']);
        }

        if (!empty($field['disabled'])) {
            $inputField->disabled($field['disabled']);
        }

        if (!empty($field['readonly'])) {
            $inputField->readonly($field['readonly']);
        }

        // Attributs de validation et contraintes (Évite le piège du zero/empty)
        foreach (['maxlength', 'min', 'max', 'step', 'pattern'] as $attribute) {
            if (isset($field[$attribute]) && $field[$attribute] !== '') {
                $inputField->attribute($attribute, (string) $field[$attribute]);
            }
        }

        // Optimisation de l'UX Mobile via l'inputmode
        $inputMode = match ($fieldType) {
            'number' => 'numeric',
            'tel' => 'tel',
            'email' => 'email',
            'url' => 'url',
            'date' => 'date',
            default => null,
        };

        if ($inputMode !== null) {
            $inputField->attribute('inputmode', $inputMode);
        }

        return $inputField;
    }
}