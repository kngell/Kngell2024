<?php

declare(strict_types=1);

trait FormFieldTrait
{
    protected string $name;
    protected mixed $value;
    protected mixed $placeholder;
    protected string $accept;
    protected bool $required;
    protected bool $disabled;
    protected bool $checked;
    protected string $defaultValue;
    protected string $htmlBlock;
    protected array $formErrors = [];
    protected array $formValues = [];
    protected string $errorMessage = '';
    protected bool $novalidate;

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function value(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function placeholder(mixed $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function accept(string $accept): static
    {
        $this->accept = $accept;
        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;
        return $this;
    }

    public function disabled(bool $disabled = true): static
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function checked(bool $checked = true): static
    {
        $this->checked = $checked;
        return $this;
    }

    public function defaultValue(string $defaultValue): static
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public function hasDefaultValue(): bool
    {
        return isset($this->defaultValue) && $this->defaultValue !== '';
    }

    public function formErrors(array $formErrors): static
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    public function formValues(array $formValues): static
    {
        $this->formValues = $formValues;
        return $this;
    }

    public function errorMessage(string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function hasErrorMessage(): bool
    {
        return !empty($this->errorMessage);
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function setNovalidate(bool $novalidate = true): static
    {
        $this->novalidate = $novalidate;

        return $this;
    }

    // ── Form Helpers (unchanged) ────────────────────────────────────

    protected function inputValue(string $name, mixed $default = null): mixed
    {
        if (!isset($this->formValues) || !is_array($this->formValues)) {
            return $default;
        }
        if (str_contains($name, '.')) {
            return $this->getNestedValue($name, $default);
        }
        return array_key_exists($name, $this->formValues)
            ? $this->sanitizeFormValue($name)
            : $default;
    }

    private function getNestedValue(string $path, mixed $default = null): mixed
    {
        $keys = explode('.', $path);
        $value = $this->formValues;
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }
        return $value;
    }

    private function sanitizeFormValue(string $name): mixed
    {
        if (str_contains($name, 'password')) {
            return '';
        }
        $val = $this->formValues[$name];
        if (is_string($val) && DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $val) !== false) {
            return (new DateTimeImmutable($val))->format('Y-m-d');
        }
        return $val;
    }

    private function isValidClass(string $type = ''): string
    {
        if (
            isset($this->formValues[$this->name])
            && !str_contains($this->name, 'password')
            && $type !== 'submit'
        ) {
            return 'is-valid';
        }
        return '';
    }
}