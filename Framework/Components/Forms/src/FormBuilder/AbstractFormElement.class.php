<?php

declare(strict_types=1);
abstract class AbstractFormElement extends AbstractForm
{
    protected CollectionInterface $children;

    public function __construct()
    {
        $this->children = new Collection();
    }

    /**
     * @param AbstractForm|null ...$formElements
     * @return AbstractForm
     */
    #[Override]
    public function add(AbstractForm|null ...$formElements) : AbstractForm
    {
        /** @var AbstractForm $formElement */
        foreach ($formElements as $formElement) {
            ! is_null($formElement) ? $this->addFormElement($formElement) : '';
        }
        return $this;
    }

    public function remove(AbstractForm $component): self
    {
        $this->children->removeByValue($component);
        $component->setParent(null);
        return $this;
    }

    public function isComposite(): bool
    {
        return true;
    }

    protected function formElementAttribute(string $key, string|array|bool|int $value) : string
    {
        return match (true) {
            is_string($value) || is_int($value) => ' ' . $key . '="' . $value . '"',
            is_bool($value) => ' ' . $key,
            is_array($value) && $key === 'class' => ' class="' . implode(' ', $value) . '"',
            is_array($value) && $key === 'style' => ' style="' . implode('; ', $value) . '"',
            default => ''
        };
    }

    private function addFormElement(AbstractForm $component): self
    {
        $this->children->add($component);
        $component->setParent($this);
        return $this;
    }
}