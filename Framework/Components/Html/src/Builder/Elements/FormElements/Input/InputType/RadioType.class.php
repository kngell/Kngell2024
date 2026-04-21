<?php

declare(strict_types=1);

class RadioType extends AbstractInput
{
    protected const string TYPE = 'radio';

    protected function populateField(): void
    {
        $submittedValue = $this->inputValue($this->name, $this->defaultValue ?? '');
        $radioValue = $this->value ?? '';

        if ($radioValue !== '' && strtolower((string) $radioValue) === strtolower((string) $submittedValue)) {
            $this->checked(true);
        }
    }
}