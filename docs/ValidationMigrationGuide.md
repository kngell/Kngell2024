# Validation System Migration Guide

## Overview

This guide shows how to migrate from the old validation system to the new improved validation system.

## Quick Migration Steps

### 1. Add the ValidationTrait to your controller

**Before:**

```php
class MyController extends Controller
{
    // ...
}
```

**After:**

```php
class MyController extends Controller
{
    use ValidationTrait;
    // ...
}
```

### 2. Update validation calls

**Before:**

```php
public function create(): Response
{
    $data = $this->request->getPost()->getAll();
    $errors = $this->validator->validate($data, 'rules', $this->model);
    $form = $this->frm->make('form-name', $data, $errors);

    if (!$this->session->exists('form')) {
        $this->session->set('form', $form);
    }

    if (!empty($errors)) {
        $this->flash->add('Fields Errors... Please check.', FlashType::WARNING);
        return $this->redirect('/form');
    }

    // Process data...
}
```

**After:**

```php
public function create(): Response
{
    $data = $this->request->getPost()->getAll();

    // Simple validation with automatic error handling
    $validationResult = $this->validateFormData($data, 'rules', $this->model);

    if ($validationResult->hasErrors()) {
        return $this->redirect('/form');
    }

    // Use validated data for security
    $validatedData = $validationResult->getValidatedData();
    // Process validated data...
}
```

## Migration Patterns

### Pattern 1: Basic Form Validation

**Old Way:**

```php
$data = $this->request->getPost()->getAll();
$errors = $this->validator->validate($data, 'register', $this->user);
$form = $this->frm->make('register', $data, $errors);

if (!$this->session->exists('form')) {
    $this->session->set('form', $form);
}

if (!empty($errors)) {
    $this->flash->add('Fields Errors... Please check.', FlashType::WARNING);
    return $this->redirect('/signup');
}
```

**New Way:**

```php
$data = $this->request->getPost()->getAll();
$validationResult = $this->validateFormData($data, 'register', $this->user);

if ($validationResult->hasErrors()) {
    return $this->redirect('/signup');
}

$validatedData = $validationResult->getValidatedData();
```

### Pattern 2: Validation with File Upload

**Old Way:**

```php
$data = $this->request->getPost()->getAll();
$errors = $this->validator->validate($data, 'profile', $this->user);
$this->imgUpload->proceed(false);
$errors = array_merge($errors, $this->imgUpload->getErrors());
// ... handle errors
```

**New Way:**

```php
$data = $this->request->getPost()->getAll();
$this->imgUpload->proceed(false);
$imageErrors = $this->imgUpload->getErrors();

$validationResult = $this->validateFormData($data, 'profile', $this->user, $imageErrors);

if ($validationResult->hasErrors() || !empty($imageErrors)) {
    return $this->redirect('/profile/edit');
}
```

### Pattern 3: Simple Validation (No Form Storage)

**Old Way:**

```php
$data = $this->request->getPost()->getAll();
$errors = $this->validator->validate($data, 'login');

if (!empty($errors)) {
    $this->flash->add('Login failed', FlashType::WARNING);
    return $this->redirect('/login');
}
```

**New Way:**

```php
$data = $this->request->getPost()->getAll();
$validationResult = $this->quickValidate($data, 'login');

if ($validationResult->hasErrors()) {
    $this->handleValidationErrors($validationResult);
    return $this->redirect('/login');
}
```

## Benefits of the New System

### 1. **Better Error Handling**

- Automatic error counting and messaging
- Single vs multiple error handling
- Consistent flash messages

### 2. **Security Improvements**

- Use validated data instead of raw input
- Automatic input sanitization option
- Type-safe validation results

### 3. **Cleaner Code**

- Less boilerplate code
- Consistent patterns across controllers
- Reusable validation methods

### 4. **Enhanced Features**

- Validation groups for partial validation
- Custom validation configurations
- Rich validation result objects

## Advanced Usage

### Custom Error Messages

```php
$validationResult = $this->validateFormData($data, 'register', $this->user);

if ($validationResult->hasErrors()) {
    $this->handleValidationErrorsWithCustomMessage(
        $validationResult,
        [],
        'Registration Error',
        'Please fix all registration errors before continuing.'
    );
    return $this->redirect('/register');
}
```

### Partial Field Validation (AJAX)

```php
$fieldsToValidate = ['email', 'username'];
$validationResult = $this->validateFields($data, 'register', $fieldsToValidate);

return $this->jsonResponse($this->getValidationErrorsForJson($validationResult));
```

### Custom Validation Configuration

```php
$config = new ValidationConfig(
    stopOnFirstError: true,
    sanitizeInput: true,
    skipMissingFields: true
);

$validationResult = $this->validateWithConfig($data, 'rules', $config, $this->model);
```

## Backward Compatibility

If you need to maintain backward compatibility temporarily:

```php
// Use legacy method that returns array|bool
$legacyResult = $this->validator->validateLegacy($data, 'rules', $this->model);

if ($legacyResult === true) {
    // Validation passed
} else {
    // $legacyResult contains errors array
}
```

## Testing Your Migration

1. **Test all form submissions** to ensure validation still works
2. **Check error messages** are displayed correctly
3. **Verify validated data** is being used instead of raw input
4. **Test file upload forms** if applicable
5. **Check AJAX validation** endpoints if you have them

## Common Issues and Solutions

### Issue: Form not displaying errors

**Solution:** Make sure you're using `validateFormData()` which automatically stores the form with errors.

### Issue: Getting raw data instead of validated data

**Solution:** Use `$validationResult->getValidatedData()` instead of the original `$data` array.

### Issue: Custom error messages not showing

**Solution:** Use `handleValidationErrorsWithCustomMessage()` for custom messaging.

### Issue: File upload errors not combined

**Solution:** Pass file upload errors as the 4th parameter to `validateFormData()`.

## Next Steps

1. Update one controller at a time
2. Test thoroughly after each migration
3. Consider using the new validation features like groups and custom configs
4. Update your validation rules YAML files to use new validator features
5. Consider creating custom validators for complex business logic
