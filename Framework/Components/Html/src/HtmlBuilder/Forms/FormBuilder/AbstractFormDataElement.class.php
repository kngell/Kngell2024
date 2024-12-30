<?php

declare(strict_types=1);

abstract class AbstractFormDataElement extends AbstractHtmlComponent
{
    protected string $name;
    protected mixed $value;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $name
     * @return self
     */
    public function name(string $name): self
    {
        return $this;
    }

    /**
     * @param mixed $value
     * @return self
     */
    public function value(mixed $value): self
    {
        return $this;
    }

    protected function inputErrors(string $name) : string
    {
        $errorStr = '';
        if (isset($this->formErrors) && array_key_exists($name, $this->formErrors)) {
            foreach ($this->formErrors[$name] as $error) {
                $errorStr .= $error;
            }
            isset($this->class) ? array_push($this->class, 'is-invalid') : $this->class = ['is-invalid'];
        }
        return $errorStr;
    }

    protected function inputValue(string $name, mixed $value) : mixed
    {
        $value = $value;
        if (isset($this->formValues) && array_key_exists($name, $this->formValues)) {
            $value = $this->imputVal($name);
        }
        return $value;
    }

    protected function classStyle(string $type = '') : void
    {
        if (isset($this->class)) {
            foreach ($this->class as $key => $class) {
                if ($class === 'is-invalid') {
                    unset($this->class[$key]);
                }
            }
            $this->class[] = $this->isValidClass($type);
        } else {
            $this->class = [$this->isValidClass($type)];
        }
    }

    private function imputVal(string $name) : mixed
    {
        if (! str_contains($name, 'password')) {
            return $this->formValues[$name];
        }
        return '';
    }

    private function isValidClass(string $type = '') : string
    {
        if (isset($this->formValues[$this->name]) && ! str_contains($this->name, 'password')) {
            if (isset($type) || $type !== 'submit') {
                return 'is-valid';
            }
        }
        return '';
    }
}