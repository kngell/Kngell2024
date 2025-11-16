<?php

declare(strict_types=1);
// Define a minimal contract for currency lookups

interface CurrencyLookupInterface
{
    /**
     * Finds a Currency entity by its primary ID.
     *
     * @param int $currencyId
     *
     * @return object|null A Currency entity object.
     */
    public function findCurrencyById(int $currencyId): ?object;
}