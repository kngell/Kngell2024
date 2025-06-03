<?php

declare(strict_types=1);

interface PaymentGatewayInterface
{
    public function pay(array $cartItems, string $currency): string;

    public function refund(string $transactionId, float $amount): string;

    public function getTransactionDetails(string $transactionId): array;

    public function getResponse(): mixed;

    public function isSuccess() : bool;
}