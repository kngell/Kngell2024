<?php

declare(strict_types=1);

class PhonesHomePageTemplatePaths implements TemplatePathsInterface
{
    private string $templatePath = APP . 'Display' . DS . 'Brand' . DS . 'Phones' . DS . 'Templates' . DS;
    private string $partialsPath = VIEW . 'client' . DS . 'brand' . DS . 'phones' . DS . 'partials' . DS;

    public function Paths(): CollectionInterface
    {
        return new Collection(array_merge($this->phoneTemplatesPath(), $this->phonesFilesPath()));
    }

    private function phoneTemplatesPath() : array
    {
        return [
            'productTemplatePath' => $this->templatePath . 'productsTemplate.php',
            'bannerTemplatePath' => $this->templatePath . 'bannerTemplate.php',
            'newProductTemplate' => $this->templatePath . 'newProductsTemplate.php',
            'topSalesTemplatePath' => $this->templatePath . 'topSalesTemplate.php',
            'specialPriceTemplate' => $this->templatePath . 'specialPriceTemplate.php',
            'imgGalleryTemplate' => $this->templatePath . 'imageGalleryTemplate.php',
            'productFormPath' => $this->templatePath . 'productFormTemplate.php',
            'addToCartformPath' => $this->templatePath . 'addToCartFormTemplate.php',
            'proceedToBuyFormPath' => $this->templatePath . 'productFormTemplate.php',
        ];
    }

    private function phonesFilesPath() : array
    {
        return [
            'productDetailsPath' => $this->partialsPath . '_product_details.php',
            'blogAreaPath' => $this->partialsPath . '_blog.php',
            'bannerAreaPath' => $this->partialsPath . '_banner_area.php',
            'newProductPath' => $this->partialsPath . '_new_products.php',
            'bannerAddPath' => $this->partialsPath . '_banner_adds.php',
            'topSalesPath' => $this->partialsPath . '_top_sales.php',
            'specialPricePath' => $this->partialsPath . '_special_price.php',
        ];
    }
}