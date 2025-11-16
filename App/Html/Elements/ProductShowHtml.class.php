<?php

declare(strict_types=1);

final readonly class ProductShowHtml extends AbstractHtmlTemplate
{
    public function __construct(
        HtmlBuilder $builder,
        SectionRenderer $sectionRenderer,
        IconBuilder $iconBuilder,
        ButtonBuilder $buttonBuilder,
        FlashInterface $flash,
        private Product $product,
    ) {
        parent::__construct($builder, $sectionRenderer, $iconBuilder, $buttonBuilder, $flash);
    }

    protected function buildLayout(HtmlBuilder $html, array $data): array
    {
        return [
            $html->htmlBlock($this->renderFlash()),
            $html->tag('div')->class('product-show__header')
                ->add(
                    $html->tag('h2')->content($this->product->getName()),
                    $this->renderIconText('icon-box', 'Product details'),
                ),
            $this->renderSection('General Info', $this->renderGeneralInfo()),
            $this->renderSection('Pricing', $this->renderPricing()),
            $this->renderSection('Inventory', $this->renderInventory()),
        ];
    }

    private function renderGeneralInfo(): string
    {
        return sprintf(
            '<p><strong>SKU:</strong> %s</p>
             <p><strong>Category:</strong> %s</p>
             <p><strong>Description:</strong> %s</p>',
            htmlspecialchars($this->product->getSku()),
            htmlspecialchars($this->product->getCategory()?->getName() ?? '—'),
            htmlspecialchars($this->product->getDescription()),
        );
    }

    private function renderPricing(): string
    {
        return sprintf(
            '<p><strong>Price:</strong> %s %s</p>
             <p><strong>Compare Price:</strong> %s %s</p>',
            $this->product->getCurrencySymbol(),
            number_format($this->product->getPrice(), 2),
            $this->product->getCurrencySymbol(),
            number_format($this->product->getComparePrice(), 2),
        );
    }

    private function renderInventory(): string
    {
        return sprintf(
            '<p><strong>Stock:</strong> %d</p>
             <p><strong>Status:</strong> %s</p>',
            $this->product->getStockQuantity(),
            htmlspecialchars($this->product->getStockStatus()->value),
        );
    }
}
