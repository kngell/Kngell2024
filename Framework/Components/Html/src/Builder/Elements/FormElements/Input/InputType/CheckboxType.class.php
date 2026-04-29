<?php

declare(strict_types=1);

class CheckboxType extends AbstractInput
{
    protected const string TYPE = 'checkbox';

    protected function populateField(): void
    {
        if (!isset($this->name)) {
            return;
        }
        $submittedValue = $this->inputValue($this->name, $this->value ?? '');

        $isChecked = !empty($submittedValue) && in_array($submittedValue, [
            'common.yes', 'yes', '1', 1, 'true', true, 'on',
        ], true);

        if ($isChecked) {
            $this->checked(true);
        }
    }
}