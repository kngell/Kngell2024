<?php

declare(strict_types=1);

use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;

class CheckoutHTMLElement extends AbstractHtml
{
    private array $cartItems = [];
    private MoneyManager $money;

    public function __construct(private HtmlBuilder $builder, array $cartItems)
    {
        $this->money = MoneyManager::getInstance();
    }

    public function display(): string
    {
        $html = $this->builder;
        return $html->tag('ul')->class('list-group mb-3')->add(
            ...$this->checkoutHtmlComponents($html)
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
            $cartItemsHtml[] = $html->tag('li')->class('list-group-item d-flex justify-content-between lh-condensed')->add(
                $html->tag('div')->add(
                    $html->tag('h6')->class('my-0')->content($cartItem->getItemName() . ' by ' . $cartItem->getBrandName()),
                    $html->tag('small')->class('text-muted')->content('Amount: ' . $cartItem->getItemQuantity())
                ),
                $html->tag('span')->class('text-muted')->content(
                    $this->money->convertToEuro($cartItem->getItemPrice() * $cartItem->getItemQuantity())
                )
            );
        }
        return $cartItemsHtml;
    }
}