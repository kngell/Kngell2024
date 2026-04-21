<?php

declare(strict_types=1);

class SelectElement extends AbstractHtmlElement
{
    public function __construct(TokenInterface $token)
    {
        parent::__construct($token);
        $this->tag = 'select';
    }

    public function option(string $key, mixed $content): static
    {
        $this->add(new SelectOption($key, $content));
        return $this;
    }
}