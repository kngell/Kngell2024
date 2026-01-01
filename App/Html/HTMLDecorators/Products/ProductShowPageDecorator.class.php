<?php

declare(strict_types=1);

class ProductShowPageDecorator extends AbstractHtmlDecorator
{
    private Product $product;

    public function __construct(Controller $controller, Product $product)
    {
        parent::__construct($controller);
        $this->product = $product;
    }

    public function page(): array
    {
        $productDetails = $this->renderProductDetails($this->product);
        $mediaSection = $this->renderMedia($this->product);
        $pricingSection = $this->renderPricing($this->product);
        $inventory = $this->renderInventory($this->product);

        return [
            'product_show' => [
                'details' => $productDetails,
                'media' => $mediaSection,
                'pricing' => $pricingSection,
                'inventory' => $inventory,
            ],
        ];
    }

    private function renderProductDetails(Product $product): string
    {
        return $this->htmlGenerator->div([
            'class' => 'card',
            'content' => sprintf(
                '<h3>General Information</h3>
                 <p><strong>Name:</strong> %s</p>
                 <p><strong>Description:</strong> %s</p>
                 <p><strong>Category:</strong> %s</p>',
                htmlspecialchars($product->getName()),
                htmlspecialchars($product->getDescription()),
                htmlspecialchars($product->getCategory()?->getName() ?? '—'),
            ),
        ]);
    }

    private function renderPricing(Product $product): string
    {
        return $this->htmlGenerator->div([
            'class' => 'card',
            'content' => sprintf(
                '<h3>Pricing</h3>
                 <p><strong>Base Price:</strong> %s %s</p>
                 <p><strong>Compare Price:</strong> %s %s</p>',
                $product->getCurrencySymbol(),
                number_format($product->getPrice(), 2),
                $product->getCurrencySymbol(),
                number_format($product->getComparePrice(), 2),
            ),
        ]);
    }

    private function renderInventory(Product $product): string
    {
        return $this->htmlGenerator->div([
            'class' => 'card',
            'content' => sprintf(
                '<h3>Inventory</h3>
                 <p><strong>SKU:</strong> %s</p>
                 <p><strong>Stock:</strong> %d</p>
                 <p><strong>Status:</strong> %s</p>',
                htmlspecialchars($product->getSku()),
                $product->getStockQuantity(),
                htmlspecialchars($product->getStockStatus()->value),
            ),
        ]);
    }

    private function renderMedia(Product $product): string
    {
        $main = $product->getMainImageUrl();
        return $this->htmlGenerator->div([
            'class' => 'product-show__media',
            'content' => sprintf(
                '<img src="%s" alt="%s" class="product-show__image">',
                htmlspecialchars($main),
                htmlspecialchars($product->getName()),
            ),
        ]);
    }
}