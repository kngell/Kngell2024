<?php

declare(strict_types=1);
abstract class AbstractHTMLElement_old extends AbstractHTMLPage
{
    public function __construct(?TemplatePathsInterface $paths = null)
    {
        parent::__construct($paths);
    }

    abstract public function display() : array;
}