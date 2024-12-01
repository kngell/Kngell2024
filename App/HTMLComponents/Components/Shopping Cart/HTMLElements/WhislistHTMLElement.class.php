<?php

declare(strict_types=1);
class WhislistHTMLElement extends AbstractShoppingCartHTMLElement
{
    private string $wishlistStyle;

    public function __construct(?array $params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($params, $paths);
        $this->wishlistStyle = 'style="display:none"';
    }

    public function display(): array
    {
        return[$this->whishlistItems(), $this->wishlistStyle];
    }

    /**
     * Get the value of wishlistStyle.
     */
    public function getWishlistStyle(): string
    {
        return $this->wishlistStyle;
    }

    private function whishlistItems() : string
    {
        $template = $this->getTemplate('whishlistCollectionPath');
        if ($this->cartItems->getItems()->count() > 0) {
            /** @var CollectionInterface */
            $whishlist = $this->cartItems->getItems()->filter(function ($item) {
                return $item->cartType == 'wishlist';
            });
            $this->wishlistStyle = $whishlist->count() > 0 ? 'style="display:block"' : 'style="display:none"';

            return $this->itemsHtml($whishlist, $template, 'whishlist_item', 'whishlistItemPath');
        }
        return $this->wishlistStyle = 'style="display:none"';
    }

    private function itemsHtml(CollectionInterface $collectionItems, string $template, string $replace) : string
    {
        if ($collectionItems->count() > 0) {
            $ItemsHtml = '';
            $itemHtml = $this->getTemplate('whishlistItemPath');
            foreach ($collectionItems->all() as $item) {
                if ($item->cartType == 'wishlist') {
                    $ItemsHtml .= $this->whishlistItemHtml($item, $itemHtml);
                }
            }
            return str_replace('{{' . $replace . '}}', $ItemsHtml, $template);
        }
        return '';
    }

    private function whishlistItemHtml(?object $item = null, ?string $template = null) : string
    {
        $temp = '';
        $HT = $item->regularPrice * $item->itemQty;
        $this->taxesProducts[] = $this->filterTaxe($HT, $item, $this->getAllTaxes($this->cartItems->getItems()), $item->itemQty);
        if (! is_null($item) && ! is_null($template)) {
            $temp = str_replace('{{image}}', $this->media($item), $template);
            $temp = str_replace('{{title}}', $item->title, $temp);
            $temp = str_replace('{{categorie}}', $item->categorie, $temp);
            $temp = str_replace('{{whishlist_del_frm}}', $this->shoppingItemDelForm($item, 'wishlist'), $temp);
            // $temp = str_replace('{{itemQtyForm}}', $this->shoppingItemQtyForm($item), $temp);
            // $temp = str_replace('{{itemDelItemFrom}}', $this->shoppingItemDelForm($item, 'cart'), $temp);
            $temp = str_replace('{{price}}', $this->money->getFormatedAmount(strval($item->regularPrice * $item->itemQty)), $temp);
        }

        return $temp;
    }
}