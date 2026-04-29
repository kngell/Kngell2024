<?php

declare(strict_types=1);

abstract class AbstractFieldLayout implements InputLayoutInterface
{
    abstract public function renderInput(
        array $field,
        AbstractHtmlComponent $inputElement,
        string $fieldId,
        FormBuilder $form,
        ?AbstractForm $formInstance = null,
    ): AbstractHtmlComponent;

    protected function hasValue(array $field): bool
    {
        // Check for value (works for both input and select)
        if (isset($field['value']) && $field['value'] !== '' && $field['value'] !== null) {
            return true;
        }

        return false;
    }

    protected function buildWrapperClasses(array $field, bool $hasValue): array
    {
        $classes = ['input-field'];

        if (!empty($field['wrapperClass'])) {
            if (is_array($field['wrapperClass'])) {
                $classes = array_merge($classes, $field['wrapperClass']);
            } else {
                $classes[] = $field['wrapperClass'];
            }
        }

        if (!empty($field['required'])) {
            $classes[] = 'is-required';
        }

        if ($hasValue) {
            $classes[] = 'has-value';
        }

        if (!empty($field['error'])) {
            $classes[] = 'is-error';
        }

        if (!empty($field['disabled'])) {
            $classes[] = 'is-disabled';
        }

        return $classes;
    }

    protected function buildBody(
        array $field,
        AbstractHtmlComponent $inputElement,
        string $fieldId,
        FormBuilder $form,
        ?AbstractForm $formInstance,
    ): AbstractHtmlComponent {
        $body = $form->tag('div')->class('input-field__body');

        // Add left icon
        $leftIcon = $this->buildLeftIcon($field, $form, $formInstance);
        if ($leftIcon) {
            $body->add($leftIcon);
        }

        // Build input container
        $inputContainer = $this->buildInputContainer($field, $inputElement, $fieldId, $form);
        $body->add($inputContainer);

        // Add right container (counter + right icon)
        $rightContainer = $this->buildRightContainer($field, $form, $formInstance);
        if ($rightContainer) {
            $body->add($rightContainer);
        }

        return $body;
    }

    protected function buildFooter(array $field, FormBuilder $form): ?AbstractHtmlComponent
    {
        // Support both flat footer and nested footer array
        $footerData = $field['footer'] ?? $field;

        $hasHelper = isset($footerData['hint']);
        $hasError = isset($footerData['error']);
        $hasFooterCounter = isset($footerData['counter']) || isset($footerData['maxlength']);

        if (!$hasHelper && !$hasError && !$hasFooterCounter) {
            return null;
        }

        $footer = $form->tag('div')->class('input-field__footer');
        // Helper text
        $helperText = $footerData['hint'] ?? '';
        if (!empty($helperText)) {
            $helper = $form->tag('span')
                ->class('input-field__helper')
                ->content($helperText);
            $footer->add($helper);
        } else {
            // Empty helper placeholder for consistency
            $footer->add($form->tag('span')->class('input-field__helper'));
        }

        // Error message
        $errorText = $footerData['error'] ?? '';
        $error = $form->tag('span')
            ->class('input-field__error')
            ->content($errorText);
        $footer->add($error);

        // Footer counter
        if (isset($footerData['counter'])) {
            $footerCounter = $form->tag('span')
                ->class('input-field__footer-counter')
                ->content($footerData['counter']);
            $footer->add($footerCounter);
        } elseif (isset($footerData['maxlength'])) {
            $current = $footerData['value'] ?? 0;
            $max = $footerData['maxlength'];
            $footerCounter = $form->tag('span')
                ->class('input-field__footer-counter')
                ->content("{$current}/{$max}");
            $footer->add($footerCounter);
        }

        return $footer;
    }

    private function buildInputContainer(
        array $field,
        AbstractHtmlComponent $inputElement,
        string $fieldId,
        FormBuilder $form,
    ): AbstractHtmlComponent {
        $inputContainer = $form->tag('div')->class('input-field__input-container');
        $inputContainer->add($inputElement);

        // Add label
        $labelText = $field['label'] ?? ucfirst($field['name'] ?? '');
        if (!empty($labelText)) {
            $labelClasses = ['input-field__label'];

            if (!empty($field['leftIcon'])) {
                $labelClasses[] = 'has-left-icon';
            }

            $label = $form->label($labelText)
                ->for($fieldId)
                ->class(implode(' ', $labelClasses));

            $inputContainer->add($label);
        }

        return $inputContainer;
    }

    private function buildLeftIcon(array $field, FormBuilder $form, ?AbstractForm $formInstance): ?AbstractHtmlComponent
    {
        $leftIcon = $field['leftIcon'] ?? null;

        if (empty($leftIcon)) {
            return null;
        }

        $icon = is_array($leftIcon) ? ($leftIcon['icon'] ?? '') : $leftIcon;
        $aria = is_array($leftIcon) ? ($leftIcon['aria'] ?? '') : '';
        $classes = is_array($leftIcon) ? ($leftIcon['class'] ?? []) : [];

        if (empty($icon)) {
            return null;
        }

        $iconContainer = $form->tag('div')->class('input-field__icon-left');

        $iconElement = $formInstance?->createIcon($icon, $aria, $classes);
        if ($iconElement) {
            $iconContainer->add($iconElement);
        }

        return $iconContainer;
    }

    private function buildRightContainer(array $field, FormBuilder $form, ?AbstractForm $formInstance): ?AbstractHtmlComponent
    {
        $hasCounter = isset($field['counter']);
        $rightIcon = $field['rightIcon'] ?? null;
        $hasRightIcon = !empty($rightIcon);

        if (!$hasCounter && !$hasRightIcon) {
            return null;
        }

        $rightContainer = $form->tag('div')->class('input-field__right-container');

        // Add counter
        if ($hasCounter) {
            $counter = $form->tag('span')
                ->class('input-field__counter')
                ->content($field['counter']);
            $rightContainer->add($counter);
        }

        // Add right icon
        if ($hasRightIcon) {
            $icon = is_array($rightIcon) ? ($rightIcon['icon'] ?? '') : $rightIcon;
            $aria = is_array($rightIcon) ? ($rightIcon['aria'] ?? '') : '';
            $classes = is_array($rightIcon) ? ($rightIcon['class'] ?? []) : [];

            if (!empty($icon)) {
                $iconContainer = $form->tag('div')->class('input-field__icon-right');

                $iconElement = $formInstance?->createIcon($icon, $aria, $classes);
                if ($iconElement) {
                    $iconContainer->add($iconElement);
                    $rightContainer->add($iconContainer);
                }
            }
        }

        return $rightContainer;
    }
}
