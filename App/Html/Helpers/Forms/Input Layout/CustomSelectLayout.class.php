<?php

declare(strict_types=1);

class CustomSelectLayout extends AbstractFieldLayout
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
        $wrapperClasses[] = 'custom-select';

        // Build wrapper
        $wrapper = $form->tag('div')->class(...$wrapperClasses);

        // Build body (reusing parent method)
        $body = $this->buildBody($field, $inputElement, $fieldId, $form, $formInstance);
        $wrapper->add($body);

        // Build dropdown
        $dropdown = $this->buildDropdown($field, $form, $formInstance);
        if ($dropdown) {
            $wrapper->add($dropdown);
        }

        // Build footer
        $footer = $this->buildFooter($field, $form);
        if ($footer) {
            $wrapper->add($footer);
        }

        // Hidden input for value
        $currentValue = $formInstance?->getFormValues()[$field['name']] ?? $field['value'] ?? null;
        $hiddenInput = $form->input('hidden')
            ->name($field['name'])
            ->class('input-field__hidden-value')
            ->value($currentValue);
        $wrapper->add($hiddenInput);

        return $wrapper;
    }

    private function buildDropdown(array $field, FormBuilder $form, ?AbstractForm $formInstance): ?AbstractHtmlComponent
    {
        $hasSearch = $field['searchable'] ?? true;
        // $hasOptions = !empty($field['options']) || !empty($field['apiEndpoint']);

        // if (!$hasOptions) {
        //     return null;
        // }

        $dropdown = $form->tag('div')->class('input-field__dropdown');

        // Add search group if searchable
        if ($hasSearch) {
            $searchGroup = $this->buildSearchGroup($field, $form, $formInstance);
            $dropdown->add($searchGroup);
        }

        // Build options list
        $optionList = $this->buildOptionList($field, $form, $formInstance);
        if ($optionList) {
            $dropdown->add($optionList);
        }

        return $dropdown;
    }

    private function buildSearchGroup(array $field, FormBuilder $form, ?AbstractForm $formInstance): AbstractHtmlComponent
    {
        $searchGroup = $form->tag('div')->class('search-group');

        // Icon container
        $iconContainer = $form->tag('div')->class('search-group__icon-container');

        $searchIcon = $field['searchIcon'] ?? ['icon' => 'icon-search', 'aria' => 'Search'];
        $icon = $formInstance?->createIcon(
            $searchIcon['icon'] ?? 'icon-search',
            $searchIcon['aria'] ?? 'Search',
            $searchIcon['class'] ?? [],
        );

        if ($icon) {
            $iconContainer->add($icon);
        }
        $searchGroup->add($iconContainer);

        // Search input
        $searchInput = $form->input('text')
            ->class('search-group__input-search')
            ->attribute('placeholder', $field['searchPlaceholder'] ?? 'Search...');
        $searchGroup->add($searchInput);

        return $searchGroup;
    }

    private function buildOptionList(array $field, FormBuilder $form, ?AbstractForm $formInstance): ?AbstractHtmlComponent
    {
        $options = $field['options'] ?? [];
        $optionList = $form->tag('ul')->class('option-list');

        if (empty($options)) {
            return $optionList;
        }

        $currentValue = $formInstance?->getFormValues()[$field['name']] ?? $field['value'] ?? null;

        foreach ($options as $value => $label) {
            $isSelected = $currentValue == $value;

            $optionItem = $form->tag('li')
                ->class('option-list__item' . ($isSelected ? ' selected' : ''))
                ->attribute('data-value', $value)
                ->attribute('data-label', $label)
                ->content($label);

            $optionList->add($optionItem);
        }

        return $optionList;
    }
}