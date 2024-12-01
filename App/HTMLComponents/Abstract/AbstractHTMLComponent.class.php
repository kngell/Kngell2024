<?php

declare(strict_types=1);
abstract class AbstractHTMLComponent extends AbstractHTMLPage
{
    protected ?CollectionInterface $children;
    protected array $params;

    public function __construct($params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($paths);
        $this->children = new Collection();
        $this->params = $params;
    }

    abstract public function display() : array;

    public function add(AbstractHTMLPage $htmlObj): self
    {
        $htmlObj->level = $this->level + 1;
        $this->children->add($htmlObj);
        $htmlObj->setParent($this);
        return $this;
    }

    /**
     * Get the value of children.
     */
    public function getChildren(): ?CollectionInterface
    {
        return $this->children;
    }

    /**
     * Set the value of children.
     */
    public function setChildren(?CollectionInterface $children): self
    {
        $this->children = $children;

        return $this;
    }
}