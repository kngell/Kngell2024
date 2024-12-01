<?php

declare(strict_types=1);

use Brick\Money\Money;

class ShoppingCartSubTotalHTMLElement extends AbstractShoppingCartHTMLElement
{
    private ?Money $HT = null;
    private ?string $TTC = null;
    private string $cartTaxTemplate;
    private string $proceedTobuyForm;

    public function __construct(?array $params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($params, $paths);
        $this->cartTaxTemplate = $this->getTemplate('cartTaxTemplate');
        $this->proceedTobuyForm = $this->getTemplate('proceedTobuyFormPath');
    }

    public function display(): array
    {
        return['shoppingCartSubtotal', $this->shoppingCartSubtotal(), $this->taxesProducts];
    }

    private function shoppingCartSubtotal() : string
    {
        $this->HT = $this->totalHT($this->cartItems->getItems());
        $template = $this->getTemplate('shoppingCartsubtotalPath');
        $taxTemplate = $this->cartTaxTemplate;
        $finalTaxes = $this->calcTaxes($this->taxesProducts);
        list($taxeHtml, $totalTaxes) = $this->taxesHtmlAndtotal($finalTaxes, $taxTemplate);
        $template = str_replace('{{nb_items}}', strval($this->cartItems->getItems()->filter(function ($item) {
            return $item->cartType == 'cart';
        })->count()), $template);
        $template = str_replace('{{totalHT}}', $this->HT->formatTo('fr_FR'), $template);
        $template = str_replace('{{taxResumeHtml}}', $taxeHtml, $template);
        $this->TTC = $this->HT->plus($totalTaxes)->formatTo('fr_FR');
        $template = str_replace('{{totalTTC}}', $this->TTC ?? '', $template);
        return str_replace('{{proceedTobuyform}}', $this->proceedToBuyForm(), $template);
    }

    private function proceedToBuyForm() : string
    {
        $form = $this->frm->form([
            'action' => 'proceed_to_checkout',
            'class' => ['buy-frm'],
            'id' => 'buy-frm',
        ]);
        $template = $this->proceedTobuyForm;
        $template = str_replace('{{form_begin}}', $form->begin(), $template);
        $template = str_replace('{{proceedToBuyBtn}}', $form->input([
            ButtonType::class => ['type' => 'submit', 'class' => ['button', 'buy-btn']],
        ])->content('Proceed to checkout')->noWrapper()->html(), $template);
        return str_replace('{{form_end}}', $this->frm->end(), $template);
    }
}