<?php

declare(strict_types=1);

/**
 * Example usage of the improved validation system.
 */
class ValidationUsageExample
{
    public function basicValidationExample(): void
    {
        $inputData = [
            'email' => 'user@example.com',
            'password' => 'SecurePass123!',
            'first_name' => 'John',
        ];

        // Basic validation
        $validator = new Validator();
        $result = $validator->validate($inputData, 'register');

        if ($result->isValid()) {
            echo 'Validation passed!';
            $validatedData = $result->getValidatedData();
            // Use validated data safely
        } else {
            echo 'Validation failed:';
            foreach ($result->getErrors() as $field => $errors) {
                echo "- {$field}: " . implode(', ', $errors);
            }
        }
    }

    public function quickValidationExample(): void
    {
        $inputData = ['email' => 'invalid-email'];

        // Quick validation (stops on first error)
        $validator = Validator::quick();
        $result = $validator->validate($inputData, 'register');

        if ($result->hasErrors()) {
            echo 'First error: ' . $result->getFirstError();
        }
    }

    public function configuredValidationExample(): void
    {
        $inputData = [
            'email' => 'user@example.com',
            'password' => 'weak',
        ];

        // Custom configuration
        $config = new ValidationConfig(
            stopOnFirstError: false,
            validateAllFields: true,
            sanitizeInput: true,
            skipMissingFields: true
        );

        $validator = new Validator($config);
        $result = $validator->validate($inputData, 'register');

        // Get specific field errors
        $emailErrors = $result->getErrorsFor('email');
        $passwordErrors = $result->getErrorsFor('password');
    }

    public function validationGroupsExample(): void
    {
        $inputData = [
            'email' => 'user@example.com',
            'password' => 'SecurePass123!',
        ];

        // Validate only specific rules (e.g., only required fields)
        $validator = Validator::withGroups(['required', 'valid_email']);
        $result = $validator->validate($inputData, 'register');
    }

    public function legacyCompatibilityExample(): void
    {
        $inputData = ['email' => 'user@example.com'];

        // For backward compatibility with existing code
        $validator = new Validator();
        $legacyResult = $validator->validateLegacy($inputData, 'register');

        if ($legacyResult === true) {
            echo 'Validation passed (legacy mode)';
        } else {
            // $legacyResult is an array of errors
            print_r($legacyResult);
        }
    }

    public function errorHandlingExample(): void
    {
        try {
            $validator = new Validator();
            $result = $validator->validate([], 'non-existent-rules');
        } catch (ValidationException $e) {
            echo 'Validation configuration error: ' . $e->getMessage();

            if ($e->getValidationResult()) {
                // Handle validation result if available
                $result = $e->getValidationResult();
            }
        }
    }

    public function resultAnalysisExample(): void
    {
        $validator = new Validator();
        $result = $validator->validate(['email' => 'invalid'], 'register');

        // Rich result analysis
        echo 'Valid: ' . ($result->isValid() ? 'Yes' : 'No') . "\n";
        echo 'Error count: ' . $result->getErrorCount() . "\n";
        echo 'Fields with errors: ' . implode(', ', $result->getFieldsWithErrors()) . "\n";
        echo 'Validated data: ' . json_encode($result->getValidatedData()) . "\n";

        // Convert to array for API responses
        $apiResponse = $result->toArray();
    }
}