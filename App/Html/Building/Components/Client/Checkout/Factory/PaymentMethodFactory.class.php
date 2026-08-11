<?php

declare(strict_types=1);

class PaymentMethodFactory
{
    /**
     * @return array<PaymentMethodDto>
     */
    public static function createDefaultPaymentMethods(): array
    {
        return [
            self::createCardMethod(),
            self::createPayPalMethod(),
            self::createApplePayMethod(),
            self::createGooglePayMethod(),
        ];
    }

    public static function createCardMethod(): PaymentMethodDto
    {
        return PaymentMethodDto::createCard(
            id: 'card',
            label: 'Credit / Debit Card',
            value: 'card',
            icons: ['icon-visa', 'icon-mastercard', 'icon-google-pay'],
            isDefault: true,
            fields: self::createCardFields(),
            description: 'Pay securely with your credit or debit card',
        );
    }

    public static function createPayPalMethod(): PaymentMethodDto
    {
        return PaymentMethodDto::createSimple(
            id: 'paypal',
            label: 'PayPal',
            value: 'paypal',
            content: 'After clicking <strong>Complete Purchase</strong>, you\'ll be redirected to PayPal to complete your payment.',
            icons: ['icon-paypal'],
            isDefault: false,
            description: 'Fast and secure checkout with PayPal',
        );
    }

    public static function createApplePayMethod(): PaymentMethodDto
    {
        return PaymentMethodDto::createSimple(
            id: 'applepay',
            label: 'Apple Pay',
            value: 'applepay',
            content: 'Complete your purchase securely using Apple Pay.',
            icons: ['icon-apple-pay'],
            isDefault: false,
            description: 'Pay quickly and securely with Apple Pay',
        );
    }

    public static function createGooglePayMethod(): PaymentMethodDto
    {
        return PaymentMethodDto::createSimple(
            id: 'googlepay',
            label: 'Google Pay',
            value: 'googlepay',
            content: 'Complete your purchase securely using Google Pay.',
            icons: ['icon-google-pay'],
            isDefault: false,
            description: 'Pay quickly and securely with Google Pay',
        );
    }

    /**
     * @return array<FormFieldConfig>
     */
    private static function createCardFields(): array
    {
        return [
            FormFieldConfig::create(
                name: 'card_number',
                type: 'text',
                label: 'Card Number',
                id: 'card_number',
                placeholder: '1234 5678 9012 3456',
                attributes: ['autocomplete' => 'cc-number'],
            ),
            FormFieldConfig::create(
                name: 'card_name',
                type: 'text',
                label: 'Cardholder Name',
                id: 'card_name',
                attributes: ['autocomplete' => 'cc-name'],
            ),
            FormFieldConfig::create(
                name: 'expiry',
                type: 'text',
                label: 'Expiry',
                id: 'expiry',
                placeholder: 'MM / YY',
                attributes: ['autocomplete' => 'cc-exp'],
            ),
            FormFieldConfig::create(
                name: 'cvv',
                type: 'password',
                label: 'CVV',
                id: 'cvv',
                maxlength: 4,
                attributes: ['autocomplete' => 'cc-csc'],
            ),
            FormFieldConfig::create(
                name: 'save_card',
                type: 'checkbox',
                label: 'Save this card securely',
                id: 'save_card',
                defaultValue: '1',
            ),
        ];
    }
}