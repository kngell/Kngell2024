<?php

declare(strict_types=1);

interface FormatterInterface
{
    public function formatNumber(float $number, array $format, ?int $decimals = null): string;

    public function formatCurrency(float $amount, string $currencyCode, array $numberFormat): string;

    public function formatDate(DateTimeInterface $date, string $format): string;

    public function formatDateTime(DateTimeInterface $dateTime, string $format): string;
}