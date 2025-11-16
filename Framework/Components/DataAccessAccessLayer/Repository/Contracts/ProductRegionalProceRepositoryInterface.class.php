<?php

declare(strict_types=1);

interface ProductRegionalPriceRepositoryInterface
{
    public function findByProduct(int $productId, bool $activeOnly = true): array;

    public function findByProductAndRegion(int $productId, string $regionCode): ?ProductRegionalPrice;

    public function findActiveSales(string $regionCode): void;

    public function updateRegionalPrices(int $productId, array $regionalPrices): bool;
}