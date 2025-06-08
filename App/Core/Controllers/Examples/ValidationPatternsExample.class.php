<?php

declare(strict_types=1);

/**
 * Example controller showing different validation patterns using the new system.
 */
class ValidationPatternsExample extends Controller
{
    use ValidationTrait;

    public function __construct(
        private ValidatorInterface $validator,
        private UserFormCreator $frm,
        private UserModel $user
    ) {
    }

    /**
     * Pattern 1: Basic form validation with redirect on error.
     */
    public function basicValidationPattern(): Response
    {
        $data = $this->request->getPost()->getAll();

        // Simple validation with automatic error handling
        $validationResult = $this->validateFormData($data, 'register', $this->user);

        if ($validationResult->hasErrors()) {
            return $this->redirect('/form');
        }

        // Use validated data
        $validatedData = $validationResult->getValidatedData();
        // Process validated data...

        return $this->redirect('/success');
    }

    /**
     * Pattern 2: Validation with file upload errors.
     */
    public function validationWithFileUpload(): Response
    {
        $data = $this->request->getPost()->getAll();

        // Process file upload
        $fileErrors = $this->processFileUpload();

        // Validate with additional errors
        $validationResult = $this->validateFormData($data, 'profile', $this->user, $fileErrors);

        if ($validationResult->hasErrors() || ! empty($fileErrors)) {
            return $this->redirect('/profile/edit');
        }

        $validatedData = $validationResult->getValidatedData();
        // Save profile...

        return $this->redirect('/profile');
    }

    /**
     * Pattern 3: Quick validation for simple forms.
     */
    public function quickValidationPattern(): Response
    {
        $data = $this->request->getPost()->getAll();

        // Quick validation without form storage
        $validationResult = $this->quickValidate($data, 'login', $this->user);

        if ($validationResult->hasErrors()) {
            $this->handleValidationErrors($validationResult);
            return $this->redirect('/login');
        }

        // Process login...
        return $this->redirect('/dashboard');
    }

    /**
     * Pattern 4: Validation with custom configuration.
     */
    public function customConfigValidation(): Response
    {
        $data = $this->request->getPost()->getAll();

        // Custom validation config (stop on first error)
        $config = new ValidationConfig(
            stopOnFirstError: true,
            sanitizeInput: true
        );

        $validationResult = $this->validateWithConfig($data, 'register', $config, $this->user);

        if ($validationResult->hasErrors()) {
            // Custom error handling
            $this->handleValidationErrorsWithCustomMessage(
                $validationResult,
                [],
                'Registration Error',
                'Please fix the registration errors and try again.'
            );
            return $this->redirect('/register');
        }

        return $this->redirect('/welcome');
    }

    /**
     * Pattern 5: Validation with groups (partial validation).
     */
    public function partialValidation(): Response
    {
        $data = $this->request->getPost()->getAll();

        // Validate only specific fields (e.g., for AJAX requests)
        $fieldsToValidate = ['email', 'password'];
        $validationResult = $this->validateFields($data, 'register', $fieldsToValidate, $this->user);

        if ($validationResult->hasErrors()) {
            // Return JSON response for AJAX
            return $this->jsonResponse($this->getValidationErrorsForJson($validationResult));
        }

        return $this->jsonResponse(['valid' => true]);
    }

    /**
     * Pattern 6: One-liner validation with redirect.
     */
    public function oneLineValidation(): Response|ValidationResult
    {
        $data = $this->request->getPost()->getAll();

        // Validate and redirect on error in one line
        $result = $this->validateAndRedirectOnError($data, 'contact', '/contact');

        // If we get here, validation passed
        if ($result instanceof ValidationResult) {
            $validatedData = $result->getValidatedData();
            // Process contact form...
            $this->flash->add('Message sent successfully!');
            return $this->redirect('/contact/success');
        }

        return $result; // This is the redirect response
    }

    /**
     * Pattern 7: API validation with JSON response.
     */
    public function apiValidation(): Response
    {
        $data = $this->request->getPost()->getAll();

        $validationResult = $this->quickValidate($data, 'api-user');

        // Return structured JSON response
        $response = $this->getValidationErrorsForJson($validationResult);

        if ($validationResult->isValid()) {
            $response['data'] = $validationResult->getValidatedData();
            $response['message'] = 'Validation successful';
        }

        return $this->jsonResponse($response);
    }

    /**
     * Pattern 8: Multi-step form validation.
     */
    public function multiStepValidation(): Response
    {
        $data = $this->request->getPost()->getAll();
        $step = $data['step'] ?? 1;

        // Validate different rules based on step
        $rules = match ($step) {
            1 => 'step1-rules',
            2 => 'step2-rules',
            3 => 'step3-rules',
            default => 'complete-form'
        };

        $validationResult = $this->validateFormData($data, $rules, $this->user);

        if ($validationResult->hasErrors()) {
            return $this->redirect("/form/step/{$step}");
        }

        // Store validated data for this step
        $this->session->set("step_{$step}_data", $validationResult->getValidatedData());

        if ($step < 3) {
            return $this->redirect('/form/step/' . ($step + 1));
        }

        // Final step - combine all data
        $allData = array_merge(
            $this->session->get('step_1_data', []),
            $this->session->get('step_2_data', []),
            $validationResult->getValidatedData()
        );

        // Process complete form...
        return $this->redirect('/form/complete');
    }

    /**
     * Helper method for file upload processing.
     */
    private function processFileUpload(): array
    {
        // Simulate file upload processing
        return []; // Return any file upload errors
    }

    /**
     * Helper method for JSON responses.
     */
    private function jsonResponse(array $data): Response
    {
        // Implementation depends on your Response class
        return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
    }
}