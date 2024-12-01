<?php

declare(strict_types=1);
class NewProductsHTMLElement extends AbstractProductsHTMLElement
{
    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        $newProductsTemplate = $this->getTemplate('newProductPath');
        $productTemplate = $this->getTemplate('newProductTemplate');
        $productTemplate = str_replace('{{singleProductTemplate}}', $this->getTemplate('productTemplatePath'), $productTemplate);
        return['html', $this->iteratedOutput($newProductsTemplate, $productTemplate)];
    }
}