<?php

declare(strict_types=1);
class SpecialPriceHTMLElement extends AbstractProductsHTMLElement
{
    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        $brandButton = $this->categoriesButton();
        $specialTemplate = $this->getTemplate('specialPricePath');
        $productTemplate = $this->getTemplate('specialPriceTemplate');
        $specialTemplate = str_replace('{{brandButton}}', ! empty($brandButton) ? implode('', $brandButton) : '', $specialTemplate);
        $productTemplate = str_replace('{{singleProductTemplate}}', $this->getTemplate('productTemplatePath'), $productTemplate);

        return ['html', $this->iteratedOutput($specialTemplate, $productTemplate)];
    }

    private function categoriesButton() :  array
    {
        $brandButton = [];
        $products = $this->params['products'];
        if ($products->count() > 0) {
            $brands = array_unique(array_map(function ($prod) {
                return $prod->categorie;
            }, $products->all()));
            sort($brands);
            if (isset($brands)) {
                $brandButton = array_map(function ($brand) {
                    return sprintf('<button class="btn" data-filter=".%s">%s</button>', $brand, $brand);
                }, $brands);
            }
        }
        return $brandButton;
    }
}