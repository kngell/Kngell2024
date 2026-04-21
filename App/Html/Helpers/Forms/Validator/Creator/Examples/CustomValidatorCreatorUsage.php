<?php

declare(strict_types=1);

// Example 1: Add custom rule to a specific validator instance
$validator = Validator::create();

// Add a custom rule for product validation
$validator->addCustomRule('unique_sku', function (string $display, mixed $inputValue, mixed $ruleValue) {
    // Check if SKU is unique in database
    $exists = Product::where('sku', $inputValue)->exists();

    if ($exists) {
        return "<div class='input-box__hint-text invalid-feedback'>" .
               htmlspecialchars("{$display} must be unique") .
               '</div>';
    }

    return false;
});

$result = $validator->validate($productData, 'productRules');

// Example 2: Custom date format validation
$validator->addCustomRule('date_format', function (string $display, mixed $inputValue, mixed $ruleValue) {
    $format = $ruleValue ?: 'Y-m-d';
    $date = DateTime::createFromFormat($format, $inputValue);

    if (!$date || $date->format($format) !== $inputValue) {
        return "<div class='input-box__hint-text invalid-feedback'>" .
               htmlspecialchars("{$display} must be in format: {$format}") .
               '</div>';
    }

    return false;
});

// Example 3: Custom file type validation
$validator->addCustomRule('allowed_mimes', function (string $display, mixed $inputValue, mixed $ruleValue) {
    $allowedTypes = is_array($ruleValue) ? $ruleValue : explode(',', $ruleValue);
    $fileType = mime_content_type($inputValue['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        $types = implode(', ', $allowedTypes);
        return "<div class='input-box__hint-text invalid-feedback'>" .
               htmlspecialchars("{$display} must be one of: {$types}") .
               '</div>';
    }

    return false;
});