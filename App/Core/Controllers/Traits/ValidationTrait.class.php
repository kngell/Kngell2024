<?php

declare(strict_types=1);

/**
 * Trait for handling validation in controllers
 * Provides consistent validation patterns across all controllers.
 */
trait ValidationTrait
{
    /**
     * Validate form data and handle errors consistently.
     */
    protected function validateFormData(
        array $data,
        string $rules,
        ?Model $model = null,
        array $additionalErrors = []
    ): ValidationResult {
        $validationResult = $this->validator->validate($data, $rules, $model);

        // Store combined errors for form rendering
        $allErrors = $this->combineValidationErrors($validationResult, $additionalErrors);

        // Create and store form with errors
        $this->storeFormWithErrors($rules, $data, $allErrors);

        // Handle error messaging
        if ($validationResult->hasErrors() || ! empty($additionalErrors)) {
            $this->handleValidationErrors($validationResult, $additionalErrors);
        }

        return $validationResult;
    }

    /**
     * Quick validation for simple forms without additional processing.
     */
    protected function quickValidate(array $data, string $rules, ?Model $model = null): ValidationResult
    {
        return $this->validator->validate($data, $rules, $model);
    }

    /**
     * Validate with custom configuration.
     */
    protected function validateWithConfig(
        array $data,
        string $rules,
        ValidationConfig $config,
        ?Model $model = null
    ): ValidationResult {
        $validator = new Validator($config);
        return $validator->validate($data, $rules, $model);
    }

    /**
     * Combine validation errors with additional errors (like file upload errors).
     */
    protected function combineValidationErrors(ValidationResult $validationResult, array $additionalErrors): array
    {
        $errors = $validationResult->getErrors();

        if (! empty($additionalErrors)) {
            $errors = array_merge($errors, $additionalErrors);
        }

        return $errors;
    }

    /**
     * Store form with errors in session for redisplay.
     */
    protected function storeFormWithErrors(string $formType, array $data, array $errors): void
    {
        if (! $this->session->exists('form')) {
            $form = $this->frm->make($formType, $data, $errors);
            $this->session->set('form', $form);
        }
    }

    /**
     * Handle validation errors with improved messaging.
     */
    protected function handleValidationErrors(ValidationResult $validationResult, array $additionalErrors = []): void
    {
        $validationErrorCount = $validationResult->getErrorCount();
        $additionalErrorCount = count($additionalErrors);
        $totalErrors = $validationErrorCount + $additionalErrorCount;

        if ($totalErrors === 0) {
            return;
        }

        if ($totalErrors === 1) {
            // Show specific error for single validation error
            $firstError = $validationResult->getFirstError() ?? reset($additionalErrors);
            $this->flash->add($firstError, FlashType::WARNING);
        } else {
            // Show summary for multiple errors
            $this->flash->add(
                "Please correct the {$totalErrors} validation errors below.",
                FlashType::WARNING
            );
        }
    }

    /**
     * Handle validation errors with custom messages.
     */
    protected function handleValidationErrorsWithCustomMessage(
        ValidationResult $validationResult,
        array $additionalErrors = [],
        string $singleErrorPrefix = '',
        string $multipleErrorMessage = ''
    ): void {
        $totalErrors = $validationResult->getErrorCount() + count($additionalErrors);

        if ($totalErrors === 0) {
            return;
        }

        if ($totalErrors === 1) {
            $firstError = $validationResult->getFirstError() ?? reset($additionalErrors);
            $message = $singleErrorPrefix ? $singleErrorPrefix . ': ' . $firstError : $firstError;
            $this->flash->add($message, FlashType::WARNING);
        } else {
            $message = $multipleErrorMessage ?: "Please correct the {$totalErrors} validation errors below.";
            $this->flash->add($message, FlashType::WARNING);
        }
    }

    /**
     * Get validated data safely, with fallback to original data.
     */
    protected function getValidatedDataSafely(ValidationResult $validationResult, array $originalData): array
    {
        if ($validationResult->isValid()) {
            return $validationResult->getValidatedData();
        }

        // Return original data if validation failed (for backward compatibility)
        return $originalData;
    }

    /**
     * Check if validation passed and handle redirect if failed.
     */
    protected function validateAndRedirectOnError(
        array $data,
        string $rules,
        string $redirectPath,
        ?Model $model = null,
        array $additionalErrors = []
    ): ValidationResult|Response {
        $validationResult = $this->validateFormData($data, $rules, $model, $additionalErrors);

        if ($validationResult->hasErrors() || ! empty($additionalErrors)) {
            return $this->redirect($redirectPath);
        }

        return $validationResult;
    }

    /**
     * Validate specific fields only (useful for AJAX validation).
     */
    protected function validateFields(array $data, string $rules, array $fieldsToValidate, ?Model $model = null): ValidationResult
    {
        // Filter data to only include specified fields
        $filteredData = array_intersect_key($data, array_flip($fieldsToValidate));

        $config = new ValidationConfig(
            stopOnFirstError: false,
            validateAllFields: false,
            skipMissingFields: true
        );

        return $this->validateWithConfig($filteredData, $rules, $config, $model);
    }

    /**
     * Get validation errors formatted for JSON responses.
     */
    protected function getValidationErrorsForJson(ValidationResult $validationResult, array $additionalErrors = []): array
    {
        $errors = $validationResult->getErrors();

        // Add additional errors
        foreach ($additionalErrors as $field => $fieldErrors) {
            if (isset($errors[$field])) {
                $errors[$field] = array_merge($errors[$field], (array) $fieldErrors);
            } else {
                $errors[$field] = (array) $fieldErrors;
            }
        }

        return [
            'valid' => $validationResult->isValid() && empty($additionalErrors),
            'errors' => $errors,
            'error_count' => $validationResult->getErrorCount() + count($additionalErrors),
            'first_error' => $validationResult->getFirstError() ?? reset($additionalErrors),
        ];
    }
}