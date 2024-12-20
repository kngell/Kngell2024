<?php

declare(strict_types=1);

abstract class AbstractFormDataElement extends AbstractFormElement
{
    protected string $name;
    protected mixed $value;

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
            array_pop($this->class);
            $this->class[] = 'is-invalid';
        }
        return $errorStr;
    }

    protected function inputValue(string $name, mixed $value) : mixed
    {
        $value = $value;
        if (isset($this->formValues) && array_key_exists($name, $this->formValues)) {
            $value = $this->formValues[$name];
        }
        return $value;
    }

    protected function classStyle() : void
    {
        if (isset($this->class)) {
            foreach ($this->class as $key => $class) {
                if ($class === 'is-invalid') {
                    unset($this->class[$key]);
                }
            }
            isset($this->formValues[$this->name]) ? $this->class[] = 'is-valid' : '';
        } else {
            isset($this->formValues[$this->name]) ? $this->class = ['is-valid'] : '';
        }
    }
}