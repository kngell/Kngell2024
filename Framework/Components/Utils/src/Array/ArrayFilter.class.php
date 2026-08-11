<?php

declare(strict_types=1);

final class ArrayFilter
{
    /**
     * Filter out system fields from form data.
     */
    public static function filterSystemFields(array $data): array
    {
        $systemFields = [
            'csrfToken', 'frm_name', 'form_name',
            'form_action', '_token', '_method', 'submit',
        ];

        return array_diff_key($data, array_flip($systemFields));
    }

    /**
     * Get only form fields (exclude system fields).
     */
    public static function getFormFields(array $data): array
    {
        $systemFields = ['csrfToken', 'frm_name', 'public_id', 'form_name', 'form_action'];
        return array_diff_key($data, array_flip($systemFields));
    }

    /**
     * Clean form data by removing empty arrays.
     */
    public static function cleanFormData(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = self::cleanFormData($value);
                if (empty($value)) {
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }

    /**
     * Check if array is deeply empty.
     */
    public static function isDeepEmpty(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if (!self::isDeepEmpty($item)) {
                    return false;
                }
            }
            return true;
        }
        return empty($value);
    }
}