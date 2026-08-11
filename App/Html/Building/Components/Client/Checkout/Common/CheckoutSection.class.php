<?php

declare(strict_types=1);

enum CheckoutSection: string
{
    public function getTitle(): string
    {
        return match($this) {
            self::OPTIONS => 'How would you like to checkout?',
            self::ADDRESS => 'Shipping Address',
            self::SHIPPING => 'Shipping Method',
            self::PAYMENT => 'Payment Method',
            self::REVIEW => 'Review Order',
            self::SUMMARY => 'Order Summary'
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::OPTIONS => 'icon-options',
            self::ADDRESS => 'icon-address',
            self::SHIPPING => 'icon-shipping',
            self::PAYMENT => 'icon-payment',
            self::REVIEW => 'icon-review',
            self::SUMMARY => 'icon-summary'
        };
    }
    case OPTIONS = 'checkouOptions';
    case ADDRESS = 'checkoutAddress';
    case SHIPPING = 'checkoutShipping';
    case PAYMENT = 'checkoutPayment';
    case REVIEW = 'checkoutReview';
    case SUMMARY = 'OrderSummary';
}