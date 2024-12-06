<?php

declare(strict_types=1);

abstract class AbstractShoppingCartHTMLElement extends AbstractHTMLElement
{
    protected UserCartHTMLElement $cartItems;
    protected MoneyManager $money;
    protected FormBuilder $frm;
    protected array $userItems;
    protected array $taxesProducts = [];

    private string $shoppingDelForm;

    public function __construct(array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
        $this->cartItems = $params['userCart'];
        $this->money = $params['money'];
        $this->frm = $params['frm'];
        $this->shoppingDelForm = $this->getTemplate('shoppingDelFormPath');
    }

    abstract public function display() : array;

    /**
     * Get the value of taxesProducts.
     */
    public function getTaxesProducts(): array
    {
        return $this->taxesProducts;
    }

    /**
     * Set the value of taxesProducts.
     */
    public function setTaxesProducts(array $taxesProducts): self
    {
        $this->taxesProducts = $taxesProducts;

        return $this;
    }

    protected function filterTaxe(mixed $HT, object $product, CollectionInterface $taxes, int $itemQty) : object
    {
        $productTaxes = $taxes->filter(fn ($taxe) => $product->catId == $taxe->trCatID);
        if (property_exists($this, 'userItems')) {
            $this->userItems[] = ['id' => $product->itemId, 'HT' => $HT, 'taxes' => $productTaxes, 'itemQty' => $itemQty];
        }

        return (object) ['item' => $product->itemId, 'taxes' => $productTaxes, 'amount' => $HT];
    }

    protected function getAllTaxes(CollectionInterface $userCart) : CollectionInterface
    {
        $categories = array_unique(array_column($userCart->all(), 'catId'));
        App::getInstance()->bindParameters(abstract:TaxesFromCache::class, args:[
            'method' => 'getTaxSystem',
            'args' => $categories,
            'cacheFileName' => 'userCartTaxes',
        ], argName:'options');
        $taxes = App::diGet(TaxesFromCache::class)->get();
        App::getInstance()->remove(TaxesFromCache::class);
        return $taxes;
    }

    protected function taxesHtmlAndtotal(object $finalTaxes, string $taxeTemplate) : array
    {
        $temp = '';
        $totalTaxes = $this->money->getCustomAmt('0', 2);
        foreach ($finalTaxes as $class => $taxeParam) {
            $taxe = $this->money->getCustomAmt(strval($taxeParam['amount']), 2, $this->money->roundedDown());
            $html = str_replace('{{tax-class}}', $class ?? '', $taxeTemplate);
            $html = str_replace('{{title}}', $taxeParam['title'] ?? '', $html);
            $html = str_replace('{{tax_amount}}', $taxe->formatTo('fr_FR') ?? '', $html);
            $totalTaxes = $totalTaxes->plus($taxe->getAmount());
            $temp .= $html;
        }

        return [$temp, $totalTaxes->getAmount()];
    }

    protected function totalHT(CollectionInterface $obj) : Brick\Money\Money
    {
        if ($obj->count() > 0) {
            $price = $this->money->getCustomAmt('0', 2);
            foreach ($obj as $product) {
                if ($product->cartType == 'cart') {
                    $price = $price->plus($this->money->getCustomAmt(strval($product->regularPrice * $product->itemQty), 2));
                }
            }

            return $price;
        }

        return $this->money->getCustomAmt(strval(0), 2);
    }

    protected function calcTaxes(?array $taxesProducts) : CollectionInterface
    {
        $finalTaxes = [];
        foreach ($taxesProducts as $taxeParams) {
            foreach ($taxeParams->taxes->all() as $tax) {
                if ($tax->status == 'on') {
                    if (! array_key_exists($tax->t_class, $finalTaxes)) {
                        $finalTaxes[$tax->t_class] = [];
                    }
                    if (! array_key_exists('amount', $finalTaxes[$tax->t_class])) {
                        $finalTaxes[$tax->t_class]['amount'] = 0;
                    }
                    if (! array_key_exists($tax->t_name, $finalTaxes[$tax->t_class])) {
                        $finalTaxes[$tax->t_class]['title'] = $tax->t_name;
                    }
                    if (! array_key_exists($tax->t_name, $finalTaxes[$tax->t_class])) {
                        $finalTaxes[$tax->t_class][$tax->t_name] = $tax->t_name;
                    }
                    $finalTaxes[$tax->t_class]['amount'] += $tax->t_rate * $taxeParams->amount / 100;
                }
            }
        }

        return new Collection($finalTaxes);
    }

    protected function shoppingItemDelForm(?object $item, ?string $type) : string
    {
        if ($type == 'cart') {
            $btn = 'Sauvegarder';
            $btn_class = ['btn', 'font-baloo', 'px-3', 'border-right', 'deleteBtn'];
        } elseif ($type == 'wishlist') {
            $btn = 'Add to cart';
            $btn_class = ['btn', 'font-baloo', 'pl-0', 'pr-3', 'border-right', 'deleteBtn'];
        }

        $form = $this->frm->form([
            'action' => '#',
            'class' => ['delete-cart-item-frm'],
            'id' => 'delete-cart-item-frm' . $item->pdtId ?? 1,
        ]);
        $template = $this->shoppingDelForm;
        $template = str_replace('{{form_begin}}', $this->frm->begin(), $template);

        $template = str_replace('{{input}}', $form->input([
            HiddenType::class => ['name' => 'itemId'],
        ])->noLabel()->noWrapper()->value($item->pdtId)->html(), $template);

        $template = str_replace('{{buttonSupprimer}}', $form->input([
            ButtonType::class => ['type' => 'submit', 'class' => $btn_class],
        ])->content('Supprimer')->noWrapper()->html(), $template);

        $template = str_replace('{{buttonSauvegarder}}', $form->input([
            ButtonType::class => ['type' => 'button', 'class' => ['btn', 'save-add']],
        ])->content($btn)->noWrapper()->html(), $template);

        return str_replace('{{form_end}}', $this->frm->end(), $template);
    }
}