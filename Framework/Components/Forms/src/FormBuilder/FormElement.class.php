<?php

declare(strict_types=1);
abstract class FormElement
{
    protected FormElement $parent;
    protected array $formErrors;
    protected array $formValues;

    public function __toArray()
    {
        return call_user_func('get_object_vars', $this);
    }

    public function setParent(?self $parent)
    {
        $this->parent = $parent;
    }

    public function getParent(): self
    {
        return $this->parent;
    }

    public function addFormElement(self $component): self
    {
        return $this;
    }

    public function removeFormElement(self $component): self
    {
        return $this;
    }

    public function isComposite(): bool
    {
        return false;
    }

    abstract public function makeForm(): string;

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

    public function add(self|null ...$formElements) : self
    {
        /** @var FormElement $formElement */
        foreach ($formElements as $formElement) {
            ! is_null($formElement) ? $this->addFormElement($formElement) : '';
        }
        return $this;
    }

    /**
     * @param array $formErrors
     * @return FormElement
     */
    public function setFormErrors(array $formErrors): self
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    /**
     * @param array $formValues
     * @return FormElement
     */
    public function setFormValues(array $formValues): self
    {
        $this->formValues = $formValues;
        return $this;
    }

    protected function formElementAttribute(string $key, string|array|bool|int $value) : string
    {
        return match (true) {
            is_string($value) || is_int($value) => ' ' . $key . '="' . $value . '"',
            is_bool($value) && $key === true => ' ' . $key,
            is_array($value) && $key === 'class' => ' class="' . implode(' ', $value) . '"',
            default => ''
        };
    }

    protected function inputErrors(string $name) : string
    {
        $errorStr = '';
        if (isset($this->formErrors) && array_key_exists($name, $this->formErrors)) {
            foreach ($this->formErrors[$name] as $error) {
                $errorStr .= '<p>' . $error . '</p>';
            }
        }
        return $errorStr;
    }

    protected function inputValue(string $name, mixed $value) : mixed
    {
        $value = isset($value) ? $value : '';
        if (isset($this->formValues) && array_key_exists($name, $this->formValues)) {
            $value = $this->formValues[$name];
        }
        return $value;
    }
}