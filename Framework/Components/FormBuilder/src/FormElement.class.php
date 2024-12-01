<?php

declare(strict_types=1);
abstract class FormElement
{
    protected FormElement $parent;

    public function setParent(?self $parent)
    {
        $this->parent = $parent;
    }

    public function getParent(): self
    {
        return $this->parent;
    }

    public function add(self $component): self
    {
        return $this;
    }

    public function remove(self $component): self
    {
        return $this;
    }

    public function isComposite(): bool
    {
        return false;
    }

    abstract public function makeForm(): string;

    protected function formElementAttribute(string $key, string $value) : string
    {
        return match (true) {
            is_string($value) => ' ' . $key . '="' . $value . '"',
            is_bool($value) && $key === true => ' ' . $key,
            is_array($value) && $key === 'class' => ' class="' . implode(' ', $value) . '"',
            default => ''
        };
    }
}
