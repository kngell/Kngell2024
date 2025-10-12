<?php

declare(strict_types=1);

class Input
{
    protected const array VOID_TAGS = ['input'];
    protected const array BOOLEAN_ATTRIBUTES = [
        'checked', 'disabled', 'readonly', 'required', 'multiple',
        'hidden', 'autofocus',
    ];

    protected string $type;
    protected array $attributes = [];
    protected array $formErrors = [];
    protected array $formValues = [];

    public function __construct(string $type)
    {
        $this->type = $type;
        $this->setAttribute('type', $type);
    }

    public function generate(): string
    {
        $this->applyFormData();
        return $this->buildTag();
    }

    // Common attribute methods
    public function name(string $name): self
    {
        $this->attributes['name'] = $name;
        return $this;
    }

    public function value(mixed $value): self
    {
        $this->attributes['value'] = $value;
        return $this;
    }

    public function id(string $id): self
    {
        $this->attributes['id'] = $id;
        return $this;
    }

    public function class(string ...$classes): self
    {
        $this->attributes['class'] = array_merge($this->attributes['class'] ?? [], $classes);
        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->attributes['placeholder'] = $placeholder;
        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->attributes['required'] = $required;
        return $this;
    }

    public function readonly(bool $readonly = true): self
    {
        $this->attributes['readonly'] = $readonly;
        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->attributes['disabled'] = $disabled;
        return $this;
    }

    public function formErrors(array $formErrors): self
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    public function formValues(array $formValues): self
    {
        $this->formValues = $formValues;
        return $this;
    }

    // Input-specific methods
    public function min($value): self
    {
        $this->attributes['min'] = $value;
        return $this;
    }

    public function max($value): self
    {
        $this->attributes['max'] = $value;
        return $this;
    }

    public function step($value): self
    {
        $this->attributes['step'] = $value;
        return $this;
    }

    public function pattern(string $pattern): self
    {
        $this->attributes['pattern'] = $pattern;
        return $this;
    }

    public function autocomplete(string $autocomplete): self
    {
        $this->attributes['autocomplete'] = $autocomplete;
        return $this;
    }

    public function size(int $size): self
    {
        $this->attributes['size'] = $size;
        return $this;
    }

    public function maxlength(int $maxlength): self
    {
        $this->attributes['maxlength'] = $maxlength;
        return $this;
    }

    // Generic attribute setter for less common attributes
    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    protected function applyFormData(): void
    {
        if (isset($this->attributes['name'])) {
            $name = $this->attributes['name'];

            // Apply validation classes
            $this->applyValidationState($name);

            // Set value from form data
            if (isset($this->formValues[$name])) {
                $this->value($this->formValues[$name]);
            }
        }
    }

    protected function applyValidationState(string $name): void
    {
        $currentClasses = $this->attributes['class'] ?? [];

        // Remove existing validation classes
        $currentClasses = array_filter(
            $currentClasses,
            fn ($class) => !in_array($class, ['is-valid', 'is-invalid']),
        );

        if (isset($this->formErrors[$name])) {
            $currentClasses[] = 'is-invalid';
        } elseif (isset($this->formValues[$name]) && $this->type !== 'password') {
            $currentClasses[] = 'is-valid';
        }

        if (!empty($currentClasses)) {
            $this->attributes['class'] = array_values($currentClasses);
        }
    }

    protected function buildTag(): string
    {
        $attributes = [];

        foreach ($this->attributes as $key => $value) {
            if ($this->shouldSkipAttribute($key, $value)) {
                continue;
            }

            $attributes[] = $this->renderAttribute($key, $value);
        }

        $attributeString = $attributes ? ' ' . implode(' ', $attributes) : '';
        return "<input{$attributeString} />";
    }

    protected function shouldSkipAttribute(string $key, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_bool($value) && !$value) {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        return false;
    }

    protected function renderAttribute(string $key, mixed $value): string
    {
        // Handle boolean attributes
        if (in_array($key, self::BOOLEAN_ATTRIBUTES) && $value === true) {
            return $key;
        }

        // Handle array attributes (class, style, etc.)
        if (is_array($value)) {
            return $this->renderArrayAttribute($key, $value);
        }

        // Handle regular attributes
        return sprintf('%s="%s"', $key, htmlspecialchars((string) $value));
    }

    protected function renderArrayAttribute(string $key, array $values): string
    {
        $filtered = array_filter($values, fn ($v) => !empty($v) || $v === '0');

        if (empty($filtered)) {
            return '';
        }

        $separator = match($key) {
            'class' => ' ',
            'style' => '; ',
            default => ' '
        };

        $value = implode($separator, $filtered);
        return sprintf('%s="%s"', $key, htmlspecialchars($value));
    }
}