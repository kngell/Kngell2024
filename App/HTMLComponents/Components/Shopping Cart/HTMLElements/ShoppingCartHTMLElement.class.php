<?php

declare(strict_types=1);
class ShoppingCartHTMLElement extends AbstractShoppingCartHTMLElement
{
    private string $shoppingQtyform;
    private string $emptyCartTemplate;

    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);

        $this->shoppingQtyform = $this->getTemplate('shoppingQtyformPath');
        $this->emptyCartTemplate = $this->getTemplate('emptycartPath');
    }

    public function display(): array
    {
        return['shoppingCartItems', $this->shoppingCartItems(), $this->taxesProducts];
    }

    private function shoppingCartItems() : string
    {
        $cartItems = $this->cartItems->getItems();
        $template = $this->getTemplate('shoppingItemCollectionPath');
        if ($cartItems->count() > 0) {
            /** @var CollectionInterface */
            $cartItems = $cartItems->filter(function ($item) {
                return $item->cartType == 'cart';
            });
            if ($cartItems->count() > 0) {
                return $this->itemsHtml($cartItems, $template, 'shoppingItems');
            }
        }
        return str_replace('{{shoppingItems}}', $this->emptyCartTemplate, $template);
    }

    private function itemsHtml(CollectionInterface $collectionItems, string $template, string $replace) : string
    {
        if ($collectionItems->count() > 0) {
            $ItemsHtml = '';
            $itemHtml = $this->getTemplate('shoppingItemPath');
            foreach ($collectionItems->all() as $item) {
                if ($item->cartType == 'cart') {
                    $ItemsHtml .= $this->shoppingItemHtml($item, $itemHtml);
                }
            }
            return str_replace('{{' . $replace . '}}', $ItemsHtml, $template);
        }
        return '';
    }

    private function shoppingItemHtml(?object $item = null, ?string $template = null) : string
    {
        $temp = '';
        $HT = $item->regularPrice * $item->itemQty;
        $this->taxesProducts[] = $this->filterTaxe($HT, $item, $this->getAllTaxes($this->cartItems->getItems()), $item->itemQty);
        if (! is_null($item) && ! is_null($template)) {
            $temp = str_replace('{{image}}', $this->media($item), $template);
            $temp = str_replace('{{title}}', $item->title, $temp);
            $temp = str_replace('{{categorie}}', $item->categorie, $temp);
            $temp = str_replace('{{itemQtyForm}}', $this->shoppingItemQtyForm($item), $temp);
            $temp = str_replace('{{itemDelItemFrom}}', $this->shoppingItemDelForm($item, 'cart'), $temp);
            $temp = str_replace('{{price}}', $this->money->getFormatedAmount(strval($item->regularPrice * $item->itemQty)), $temp);
        }

        return $temp;
    }

    private function shoppingItemQtyForm(?object $item = null) : string
    {
        /** @var FormBuilder */
        $form = $this->frm->form([
            'action' => '',
            'class' => ['form_qty'],
        ])->setCsrfKey('form_qty');

        $template = $this->shoppingQtyform;

        $template = str_replace('{{form_begin}}', $form->begin(), $template);

        $template = str_replace('{{buttonUp}}', $form->input([
            ButtonType::class => ['type' => 'button', 'class' => ['qty-up', 'border', 'bg-light']],
        ])->content('<span class="qty-up-icon"></span>')->noWrapper()->html(), $template);

        $template = str_replace('{{input}}', $form->input([
            TextType::class => ['name' => 'itemQty', 'class' => ['qty_input', 'px-2', 'bg-light']],
        ])->noLabel()->noWrapper()->value($item->itemQty)->placeholder('1')->attr(['min' => '1'])->html(), $template);

        $template = str_replace('{{item}}', $form->input([
            HiddenType::class => ['name' => 'itemId', 'class' => []],
        ])->noLabel()->noWrapper()->value($item->itemId)->html(), $template);

        $template = str_replace('{{buttonDown}}', $form->input([
            ButtonType::class => ['type' => 'button', 'class' => ['qty-down', 'border', 'bg-light']],
        ])->content('<span class="qty-down-icon"></span>')->noWrapper()->html(), $template);

        return str_replace('{{form_end}}', $this->frm->end(), $template);
    }
}