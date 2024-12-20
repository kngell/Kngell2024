<?php

declare(strict_types=1);
abstract class AbstractForm
{
    protected AbstractForm|null $parent;
    protected array $formErrors = [];
    protected array $formValues = [];
    protected string $id;
    protected array $class;

    public function setParent(?self $parent)
    {
        $this->parent = $parent;
    }

    public function getParent(): self
    {
        return $this->parent;
    }

    /**
     * @param AbstractForm|null ...$formElements
     * @return AbstractForm
     */
    public function add(self|null ...$formElements) : self
    {
        return $this;
    }

    /**
     * @param AbstractForm ...$component
     * @return AbstractForm
     */
    public function remove(self $component): self
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

    /**
     * @param string $id
     * @return self
     */
    public function id(string $id): self
    {
        return $this;
    }

    /**
     * @param array $class
     * @return self
     */
    public function class(array $class): self
    {
        return $this;
    }
}
