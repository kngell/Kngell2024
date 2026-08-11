<?php

declare(strict_types=1);

final class FormProgressCalculator
{
    public function __construct(
        private readonly HtmlFormSectionManager $sectionManager,
    ) {
    }

    public function calculateCompletion(array $formValues): int
    {
        if (empty($formValues)) {
            return 0;
        }

        $values = $this->normalizeFormValues($formValues);
        $sections = $this->sectionManager->getSections($values);

        $filledFields = 0;
        $totalFields = 0;

        foreach ($sections as $sectionFields) {
            foreach ($sectionFields as $field) {
                if ($this->isCountableField($field)) {
                    $totalFields++;
                    $fieldKey = $field['key'] ?? $field['name'] ?? null;

                    if ($fieldKey && !empty($values[$fieldKey])) {
                        $filledFields++;
                    }
                }
            }
        }

        return $totalFields > 0 ? (int) (($filledFields / $totalFields) * 100) : 0;
    }

    private function normalizeFormValues($formValues): array
    {
        if (is_array($formValues)) {
            return $formValues;
        }
        if ($formValues instanceof Entity) {
            return $formValues->toArray();
        }
        return [];
    }

    private function isCountableField(array $field): bool
    {
        $nonCountableTypes = ['button', 'field-group'];
        return !in_array($field['type'] ?? 'text', $nonCountableTypes);
    }
}