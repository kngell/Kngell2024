<?php

declare(strict_types=1);

abstract class AbstractSelectHandler extends AbstractSelectLikeHandler
{
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'select';
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        return $this->buildSelectElement($field, $form, $formInstance);
    }

    protected function buildSelectElement(
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
    ): AbstractHtmlComponent {
        $fieldId = $formInstance->getFieldId($field);

        $select = $form->select()
            ->id($fieldId)
            ->class($this->getSelectClasses($field))
            ->name($field['name'])
            ->placeholder(' ');

        if (!empty($field['required'])) {
            $select->required();
        }

        if (!empty($field['disabled'])) {
            $select->disabled(true);
        }

        $this->configureSelect($select, $field, $form, $formInstance);

        $currentValue = $this->getSelectedValue($field, $formInstance);
        $options = $this->getOptionData($field, $formInstance);

        foreach ($options as $optionValue => $label) {
            $option = $form->option((string) $optionValue, (string) $label)
                ->selected($this->shouldSelectOption($currentValue, $optionValue, $field))
                ->disabled($this->isOptionDisabled($optionValue, $field));

            $this->configureOption($option, $optionValue, $label, $field, $form, $formInstance);
            $select->add($option);
        }

        return $this->decorateSelect($select, $field, $form, $formInstance);
    }

    protected function shouldSelectOption(
        ?string $currentValue,
        string|int $optionValue,
        array $field,
    ): bool {
        if ($currentValue !== null && $currentValue !== '' && (string) $currentValue === (string) $optionValue) {
            return true;
        }

        if (($currentValue === null || $currentValue === '') && $optionValue === '') {
            return true;
        }

        return false;
    }

    protected function isOptionDisabled(string|int $optionValue, array $field): bool
    {
        return false;
    }

    protected function configureSelect(
        AbstractHtmlComponent $select,
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
    ): void {
    }

    protected function configureOption(
        AbstractHtmlComponent $option,
        string|int $optionValue,
        mixed $label,
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
    ): void {
    }

    protected function decorateSelect(
        AbstractHtmlComponent $select,
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
    ): AbstractHtmlComponent {
        return $select;
    }

    abstract protected function getSelectClasses(array $field): string;
}