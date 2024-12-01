<?php

declare(strict_types=1);
abstract class FormElements extends FormElement
{
    protected CollectionInterface $children;
    protected Token $token;

    public function __construct(Token $token)
    {
        $this->token = $token;
        $this->children = new Collection();
    }

    public function add(FormElement $component): self
    {
        $this->children->add($component);
        $component->setParent($this);
        return $this;
    }

    public function remove(FormElement $component): self
    {
        $this->children->removeByValue($component);
        $component->setParent(null);
        return $this;
    }

    public function isComposite(): bool
    {
        return true;
    }
}
