<?php

declare(strict_types=1);
class TopSalesHTMLElement extends AbstractProductsHTMLElement
{
    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        $productTemplate = $this->getTemplate('topSalesTemplatePath');
        $productTemplate = str_replace('{{singleProductTemplate}}', $this->getTemplate('productTemplatePath'), $productTemplate);
        return['html', $this->iteratedOutput($this->getTemplate('topSalesPath'), $productTemplate)];
    }
}