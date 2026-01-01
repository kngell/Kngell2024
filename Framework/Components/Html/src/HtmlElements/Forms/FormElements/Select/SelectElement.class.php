<?php

declare(strict_types=1);

class SelectElement extends AbstractHtmlElement
{
    protected string $name;

    public function __construct(TokenInterface $token)
    {
        parent::__construct($token);
        $this->tag = 'select';
    }

    public function option(string $key, mixed $content): self
    {
        $this->addFormElement(new SelectOption($key, $content));
        return $this;
    }

    public function onchange(string $onchange): self
    {
        $this->onchange = $onchange;
        return $this;
    }

    /**
     * Set the value of name.
     *
     * @param string $name
     *
     * @return self
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $id
     *
     * @return SelectElement
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string ...$class
     *
     * @return SelectElement
     */
    public function class(string ...$class): self
    {
        $this->class = $class;
        return $this;
    }
}