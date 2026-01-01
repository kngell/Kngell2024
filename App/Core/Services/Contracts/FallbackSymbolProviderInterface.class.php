<?php

declare(strict_types=1);

interface FallbackSymbolProviderInterface
{
    public function getFallbackSymbol(string $currencyCode): string;

    public function getAllKnownSymbols(): array;
}