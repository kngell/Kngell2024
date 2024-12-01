<?php

declare(strict_types=1);

class AccountActivationHTMLElement extends AbstractHTMLElement
{
    public function __construct(array $params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($params, $paths);
        $this->params = $params;
    }

    public function display(): array
    {
        return [];
    }
}