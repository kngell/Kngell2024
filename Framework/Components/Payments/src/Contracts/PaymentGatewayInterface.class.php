<?php

declare(strict_types=1);

interface PaymentGatewayInterface
{
    public function pay(float $amount, string $currency): string;

    public function refund(string $transactionId, float $amount): string;

    public function getTransactionDetails(string $transactionId): array;

    public function getResponse(): mixed;
}