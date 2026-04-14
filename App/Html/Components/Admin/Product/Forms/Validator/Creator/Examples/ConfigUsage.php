<?php

declare(strict_types=1);

// Different configuration scenarios
$config = ValidationConfig::default()
    ->withSanitizeHtml(false) // Don't sanitize HTML for rich text editors
    ->withStopOnFirstError(true);

$config = ValidationConfig::strict()
    ->withSanitizeInput(false) // Don't sanitize for file uploads
    ->withMessage('email', 'Please provide a valid email address');

$config = ValidationConfig::lenient()
    ->withValidationGroups(['create', 'update']);

// In your Validator class usage:
$validator = new Validator(
    ValidationConfig::default()->withSanitizeHtml(false),
);

// Or using factory methods with custom config:
$validator = Validator::create(
    ValidationConfig::default()
        ->withSanitizeHtml(true)
        ->withStopOnFirstError(true),
);