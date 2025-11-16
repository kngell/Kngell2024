<?php

declare(strict_types=1);

use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;

class UserCartHTMLElement extends AbstractHtml
{
    private int $nbItems;
    private int $qty;
    /** @var Cart[] */
    private array $cartItems;
    private array $subtotal;
    private float $totalPrice;

    public function __construct(private HtmlBuilder $builder, array $cartItems, private MoneyManager $money)
    {
        $this->cartItems = $cartItems;
    }

    public function display(): string
    {
        $html = $this->builder;
        if (empty($this->cartItems)) {
            return $html->tag('div')->class('empty-cart__img-container')->add(
                $html->tag('img')->src($this->media('/public/assets/img/empty_cart.png'))->class('empty-cart__image')
            )->generate();
        }
        return $html->tag('div')->add(
            $html->form()->method('POST')->action('paypal/update-quantities'),
            $html->tag('table')->class('table table-hover table-responsive')->add(
                $this->tableHead($html),
                $this->tableBody($html)
            )
        )->generate();
    }

    public function getCheckoutHtmlElement(): string
    {
        $html = $this->builder;
        return $html->tag('ul')->class('list-group mb-3')->add(
            ...$this->checkoutHtmlComponents($html)
        )->generate();
    }

    /**
     * Get the value of nbItems.
     */
    public function getNbItems(): int
    {
        return $this->nbItems;
    }

    /**
     * Get the value of qty.
     */
    public function getQty(): int
    {
        return $this->qty;
    }

    /**
     * @return Cart[]
     */
    public function getCartItems(): array
    {
        return $this->cartItems;
    }

    /**
     * Get the value of subtotal.
     */
    public function getSubtotal(): array
    {
        return $this->subtotal;
    }

    /**
     * Get the value of totalPrice.
     */
    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function getPaymentSubmitBtn() : string
    {
        $form = $this->builder->form();
        return $form->method('post')->action('create-payment')->add(
            $form->input('image')->name('submit_red')->alt('Check out with PayPal')->src('https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png'),
            $form->input('hidden')->name('payment_type')->value('paypal')
        )->generate();
    }

    /**
     * @param HtmlBuilder $html
     * @return AbstractHtmlComponent[]
     * @throws NumberFormatException
     * @throws UnknownCurrencyException
     * @throws RoundingNecessaryException
     */
    private function checkoutHtmlComponents(HtmlBuilder $html) : array
    {
        $cartItemsHtml = [];
        /** @var Cart $cartItem */
        foreach ($this->cartItems as $cartItem) {
            $cartItemsHtml[] = $html->tag('li')->class('list-group-item d-flex justify-content-between lh-base')->add(
                $html->tag('div')->class('text-start')->add(
                    $html->tag('h6')->class('my-0')->content($cartItem->getItemName() . ' by ' . $cartItem->getBrandName()),
                    $html->tag('small')->class('text-muted')->content('Amount: ' . $cartItem->getItemQuantity())
                ),
                $html->tag('span')->class('text-muted')->content(
                    $this->subtotal($cartItem)
                )
            );
        }
        if (! empty($this->cartItems)) {
            $cartItemsHtml[] = $html->tag('li')->class('list-group-item d-flex justify-content-between')->add(
                $html->tag('span')->content('Total (EUR)'),
                $html->tag('strong')->content($this->totalPrice())
            );
        }

        return $cartItemsHtml;
    }

    private function tableHead(HtmlBuilder $html) : AbstractHtmlComponent
    {
        return $html->tag('thead')->add(
            $html->tag('tr')->add(
                $html->tag('th')->content('Product'),
                $html->tag('th')->content('Quantity'),
                $html->tag('th')->content('Price')->class('text-center'),
                $html->tag('th')->content('Subtotal')->class('text-center'),
                $html->tag('th')
            )
        );
    }

    private function tableBody(HtmlBuilder $html)
    {
        return  $html->tag('tbody')->add(
            ...$this->tablebodyHtmlElement($html)
        );
    }

    /**
     * @param HtmlBuilder $html
     * @return AbstractHtmlComponent[]
     * @throws FormElementNotFound
     * @throws NumberFormatException
     * @throws UnknownCurrencyException
     * @throws RoundingNecessaryException
     */
    private function tablebodyHtmlElement(HtmlBuilder $html) : array
    {
        $cartHtml = [];

        /** @var Cart $cartItem */
        foreach ($this->cartItems as $cartItem) {
            $cartHtml[] = $html->tag('tr')->add(
                $html->tag('td')->class('col-sm-8 col-md-6')->add(
                    $html->tag('div')->class('media')->add(
                        $html->tag('img')->src($this->media($cartItem->getMedia())),
                        $html->tag('div')->class('ms-2')->add(
                            $html->tag('h4')->content($cartItem->getItemName()),
                            $html->tag('h5')->content('by ' . $cartItem->getBrandName())
                        )
                    )
                ),
                $html->tag('td')->class('col-sm-1 col-md-1')->add(
                    $html->form()->method('post')->action('cart/update-quantity')->add(
                        $html->form()->input('hidden')->name('cart_id')->value($cartItem->getCartId()),
                        $html->form()->input('number')->name('item_quantity')->min(0)->class('form-control')->value($cartItem->getItemQuantity())->custom(['onchange' => 'this.form.submit()']),
                        $html->form()->input('hidden')->name('item_id')->value($cartItem->getItemId()),
                        $html->form()->button()
                    )
                ),
                $html->tag('td')->class('col-sm-1 col-md-1 text-center')->add(
                    $html->tag('strong')->content($this->money->convertToEuro($cartItem->getItemPrice()))
                ),
                $html->tag('td')->class('col-sm-1 col-md-1 text-center')->add(
                    $html->tag('strong')->content($this->subtotal($cartItem))
                ),
                $html->tag('td')->class('col-sm-1 col-md-1')->add(
                    $html->form()->action('cart/remove-from-cart')->method('post')->add(
                        $html->form()->input('hidden')->name('item_id')->value($cartItem->getItemId()),
                        $html->form()->button('submit')->content('Remove')->class('btn btn-danger')
                    )
                )
            );
        }
        $cartHtml[] = $this->htmltotalPrice($html);
        $cartHtml[] = $this->htmlButtons($html);
        return $cartHtml;
    }

    private function htmlButtons(HtmlBuilder $html) : AbstractHtmlComponent
    {
        return $html->tag('tr')->add(
            $html->tag('td'),
            $html->tag('td'),
            $html->tag('td'),
            $html->tag('td')->add(
                $html->tag('a')->href('/paypal/product')->role('button')->class('btn btn-info')->content('Continue&nbsp;shopping')
            ),
            $html->tag('td')->add(
                $html->tag('a')->href('/checkout/index')->role('button')->class('btn btn-success')->content('Checkout')
            ),
        );
    }

    private function htmltotalPrice(HtmlBuilder $html) :AbstractHtmlComponent
    {
        return $html->tag('tr')->add(
            $html->tag('td'),
            $html->tag('td'),
            $html->tag('td')->add(
                $html->form()->button('submit')->class('btn btn-warning')->custom(['form' => 'user-cart__form'])->add(
                    $html->tag('span')->content('Update&nbsp;quantities')
                )
            ),
            $html->tag('td')->class('text-right')->add(
                $html->tag('h3')->content('Total price:')
            ),
            $html->tag('td')->class('text-right')->add(
                $html->tag('h3')->add(
                    $html->tag('strong')->content($this->totalPrice())
                )
            )
        );
    }

    private function subtotal(Cart $cartItem) : string
    {
        $subtotal = $cartItem->getItemPrice() * $cartItem->getItemQuantity();
        $this->subtotal[$cartItem->getItemId()] = $subtotal;
        return $this->money->convertToEuro($subtotal);
    }

    private function totalPrice() : string
    {
        $totalPrice = 0;
        foreach ($this->subtotal as $subtotal) {
            $totalPrice += $subtotal;
        }
        $this->totalPrice = $totalPrice;
        return $this->money->convertToEuro($totalPrice);
    }
}