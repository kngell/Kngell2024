<?php

declare(strict_types=1);

class ShoppingCartPaths implements TemplatePathsInterface
{
    private string $viewPath = VIEW . 'client' . DS . 'components' . DS . 'shoppingCart' . DS . 'partials' . DS;
    private string $templatePath = APP . 'Display' . DS . 'Components' . DS . 'ShoppingCart' . DS . 'Templates' . DS;

    public function Paths(): CollectionInterface
    {
        return new Collection(array_merge($this->templatesPath(), $this->viewPath()));
    }

    private function templatesPath() : array
    {
        return [
            'shoppingItemPath' => $this->templatePath . 'shoppingItemTemplate.php',
            'shoppingItemCollectionPath' => $this->templatePath . 'shoppingItemCollectionTemplate.php',
            'shoppingQtyformPath' => $this->templatePath . 'shoppingItemQtyFormTemplate.php',
            'shoppingDelFormPath' => $this->templatePath . 'shoppingItemDelFormTemplate.php',
            'cartTaxTemplate' => $this->templatePath . 'shoppingCartTaxesTemplate.php',
            'proceedTobuyFormPath' => $this->templatePath . 'proceedToBuyFormTemplate.php',
            'whishlistCollectionPath' => $this->templatePath . 'whishlistItemCollectionTemplate.php',
            'whishlistItemPath' => $this->templatePath . 'whishlistItemTemplate.php',

        ];
    }

    private function viewPath() : array
    {
        return [
            'shoppingCartPath' => $this->viewPath . '_shopping_cart.php',
            'emptycartPath' => $this->viewPath . '_empty_shopping_cart.php',
            'shoppingCartsubtotalPath' => $this->viewPath . '_shopping_cart_subTotal.php',
            'whishlistPath' => $this->viewPath . '_shopping_whishlist.php',
        ];
    }
}
