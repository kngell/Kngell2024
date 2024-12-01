<?php

declare(strict_types=1);
abstract class AbstractHTMLElement extends AbstractHTMLPage
{
    protected array $params;

    public function __construct(array $params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($paths);
        $this->params = $params;
    }

    abstract public function display() : array;

    /**
     * Get the value of params.
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Set the value of params.
     */
    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }
}