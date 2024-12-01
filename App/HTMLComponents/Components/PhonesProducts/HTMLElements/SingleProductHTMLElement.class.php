<?php

declare(strict_types=1);
class SingleProductHTMLElement extends AbstractProductsHTMLElement
{
    private string $dayOfReplacement = '10';
    private string $warranty = '1';

    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        return ['html', $this->singleProduct()];
    }

    protected function singleProduct() : string
    {
        $product = isset($this->params['products']) ? $this->params['products'] : null;
        if (count((array) $product) !== 0) {
            return $this->outputSingleProduct($this->getTemplate('productDetailsPath'), $product);
        } else {
            return '<div class="text-center text-lead py-5">
            <h5>This product was not found</h5>
            </div>';
        }
    }

    protected function outputSingleProduct(string $template, $p) : string
    {
        $userCart = isset($this->params['userCart']) ? $this->params['userCart'] : [];
        $money = isset($this->params['money']) ? $this->params['money'] : null;
        $p->userCart = $userCart->getCartItems();
        $template = str_replace('{{title}}', $p->title ?? 'Unknown', $template);
        $template = str_replace('{{brand}}', $p->item_brand ?? 'K\'nGELL', $template);
        $template = str_replace('{{image}}', $this->media($p), $template);
        $media = is_string($p->media) ? unserialize($p->media) : $p->media;
        if (isset($media) && is_array($media) && count($media) > 0) {
            $galleryTemplate = $this->getTemplate('imgGalleryTemplate');
            $htmlGallery = '';
            foreach ($media as $img) {
                $htmlItem = str_replace('{{imageGallery}}', ImageManager::asset_img($img), $galleryTemplate);
                $htmlItem = str_replace('{{title}}', $p->title, $htmlItem);
                $htmlGallery .= $htmlItem;
            }
            $template = str_replace('{{imageGalleryTemplate}}', $htmlGallery, $template);
        }
        $template = str_replace('{{proceedToBuyForm}}', $this->productForm($p, 'Proceed to buy'), $template);
        $template = str_replace('{{addToCartForm}}', $this->productForm($p, 'Add to Cart'), $template);
        $template = str_replace('{{comparePrice}}', $money->getFormatedAmount($p->comparePrice ?? ''), $template);
        $template = str_replace('{{regularPrice}}', $money->getFormatedAmount($p->regularPrice), $template);
        $template = str_replace('{{savings}}', $money->getFormatedAmount(strval($p->comparePrice - $p->regularPrice)), $template);
        $template = str_replace('{{replacement}}', $p->replacement ?? $this->dayOfReplacement, $template);
        $template = str_replace('{{warranty}}', $p->warranty ?? $this->warranty, $template);
        $template = str_replace('{{imageUser}}', ImageManager::asset_img('users/avatar.png'), $template);
        return str_replace('{{imageUser2}}', ImageManager::asset_img('users/default-female-avatar.jpg'), $template);
    }
}