<?php

declare(strict_types=1);

class ProductRegionalPriceRepository extends Repository
{
    private const array PRODUCT_REGIONAL_PRICE = ['price_id', 'pdt_id', 'region_code', 'base_price', 'compare_price', 'cost_price', 'sale_price', 'sale_start_date', 'sale_end_date', 'currency_id', 'is_active', 'created_at', 'updated_at'];
    private const array PRODUCT_FILABLE = ['pdt_id', 'public_id', 'sku',  'slug', 'name', 'short_description',     'description',    'brand_id',   'category_id',
        'base_currency_id',        'tax_class',        'stock_quantity',
        'stock_status_id',        'is_track_stock',
        'weight', 'length',        'width',
        'height',
        'main_image',
        'status',        'is_active',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at', ];
    private const array REGION_FILABLE = ['region_code', 'region_name', 'currency_id', 'is_active', 'timezone', 'locale', 'created_at', 'updated_at'];
    private const array CURRENCY_FILABLE = ['currency_id', 'currency_code', 'currency_name', 'symbol', 'is_active', 'created_at', 'updated_at'];

    /**
     * Find regional price by product ID and region code.
     */
    public function findByProductAndRegion(int $productId, string $regionCode): ?ProductRegionalPrice
    {
        try {
            // Validate arguments
            if ($productId <= 0) {
                throw new RepositoryInvalidArgumentException('Product ID must be positive');
            }

            if (empty($regionCode)) {
                throw new RepositoryInvalidArgumentException('Region code cannot be empty');
            }

            $conditions = [
                'pdt_id' => $productId,
                'region_code' => $regionCode,
                'is_active' => true,
            ];

            $result = $this->em->createQueryBuilder()
                ->select()
                ->where($conditions)
                ->limit(1)
                ->build();

            return !empty($result) ? $result[0] : null;
        } catch (RepositoryInvalidArgumentException $e) {
            // Re-throw validation exceptions
            throw $e;
        } catch (Throwable $th) {
            // Wrap other exceptions
            throw new RepositoryException(
                "Failed to find regional price for product {$productId} and region {$regionCode}: " . $th->getMessage(),
                $th->getCode(),
                $th,
            );
        }
    }

    /**
     * Find all regional prices for a product.
     */
    // public function findByProduct(int $productId, bool $activeOnly = true): array
    // {
    //     try {
    //         if ($productId <= 0) {
    //             throw new RepositoryInvalidArgumentException('Product ID must be positive');
    //         }

    //         $conditions = ['pdt_id' => $productId];

    //         if ($activeOnly) {
    //             $conditions['is_active'] = true;
    //         }

    //         $result = $this->em->createQueryBuilder()
    //             ->select()
    //             ->where($conditions)
    //             ->build();

    //         return $result ?? [];
    //     } catch (RepositoryInvalidArgumentException $e) {
    //         throw $e;
    //     } catch (Throwable $th) {
    //         throw new RepositoryException(
    //             "Failed to find regional prices for product {$productId}: " . $th->getMessage(),
    //             $th->getCode(),
    //             $th,
    //         );
    //     }
    // }

    /**
     * Find all regional prices for a region.
     */
    // public function findByRegion(string $regionCode, bool $activeOnly = true): array
    // {
    //     try {
    //         if (empty($regionCode)) {
    //             throw new RepositoryInvalidArgumentException('Region code cannot be empty');
    //         }

    //         $conditions = ['region_code' => $regionCode];

    //         if ($activeOnly) {
    //             $conditions['is_active'] = true;
    //         }

    //         $result = $this->em->createQueryBuilder()
    //             ->select()
    //             ->where($conditions)
    //             ->build();

    //         return $result ?? [];
    //     } catch (RepositoryInvalidArgumentException $e) {
    //         throw $e;
    //     } catch (Throwable $th) {
    //         throw new RepositoryException(
    //             "Failed to find prices for region {$regionCode}: " . $th->getMessage(),
    //             $th->getCode(),
    //             $th,
    //         );
    //     }
    // }

    /**
     * Bulk update regional prices for a product.
     */
    // public function updateRegionalPrices(int $productId, array $regionalPrices): bool
    // {
    //     try {
    //         if ($productId <= 0) {
    //             throw new RepositoryInvalidArgumentException('Product ID must be positive');
    //         }

    //         if (empty($regionalPrices)) {
    //             throw new RepositoryInvalidArgumentException('Regional prices array cannot be empty');
    //         }

    //         $this->em->beginTransaction();

    //         // Deactivate existing prices for this product
    //         $this->em->createQueryBuilder()
    //             ->update()
    //             ->set(['is_active' => false])
    //             ->where(['pdt_id' => $productId])
    //             ->build();

    //         // Insert or update new prices
    //         foreach ($regionalPrices as $regionalPrice) {
    //             if (!$regionalPrice instanceof ProductRegionalPrice) {
    //                 throw new RepositoryInvalidArgumentException(
    //                     'All regional prices must be instances of ProductRegionalPrice',
    //                 );
    //             }

    //             $existing = $this->findByProductAndRegion($productId, $regionalPrice->getRegionCode());

    //             if ($existing) {
    //                 // Update existing
    //                 $this->em->createQueryBuilder()
    //                     ->update()
    //                     ->set($this->entityToArray($regionalPrice))
    //                     ->where(['price_id' => $existing->getId()])
    //                     ->build();
    //             } else {
    //                 // Insert new
    //                 $this->em->createQueryBuilder()
    //                     ->insert()
    //                     ->values($this->entityToArray($regionalPrice))
    //                     ->build();
    //             }
    //         }

    //         $this->em->commit();
    //         return true;
    //     } catch (RepositoryInvalidArgumentException $e) {
    //         $this->em->rollback();
    //         throw $e;
    //     } catch (Throwable $th) {
    //         $this->em->rollback();
    //         throw new RepositoryException(
    //             "Failed to update regional prices for product {$productId}: " . $th->getMessage(),
    //             $th->getCode(),
    //             $th,
    //         );
    //     }
    // }

    // public function findActiveSales(string $regionCode): void
    // {
    //     try {
    //         if (empty($regionCode)) {
    //             throw new RepositoryInvalidArgumentException('Region code cannot be empty');
    //         }

    //         $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

    //         $qb = $this->em->createQueryBuilder()
    //             ->selectAsAlias(self::PRODUCT_REGIONAL_PRICE)
    //             ->from('product_regional_price')
    //             ->leftJoin('product', self::PRODUCT_FILABLE)
    //             ->on('pdt_id', 'product.pdt_id')
    //             ->leftJoin('region', self::REGION_FILABLE)
    //             ->on('region_code', 'region.region_code')
    //             ->leftJoin('currency', self::CURRENCY_FILABLE)
    //             ->on(['currency_id' => 'currency.currency_id'])
    //             ->where(['region_code' => $regionCode])
    //             ->andWhere('is_active', true)
    //             ->andWhere(['sale_price', 'IS NOT', 'NULL'])
    //             ->and(function ($qb) use ($now) {
    //                 $qb->orWhere(['sale_start_date', 'IS', 'NULL'])
    //                    ->orWhere(['sale_start_date', '<=', $now]);
    //             })
    //             ->and(function ($qb) use ($now) {
    //                 $qb->orWhere(['sale_end_date', 'IS', 'NULL'])
    //                    ->orWhere(['sale_end_date', '>=', $now]);
    //             });

    //         $qb->build();
    //     } catch (RepositoryInvalidArgumentException $e) {
    //         throw $e;
    //     } catch (Throwable $th) {
    //         throw new RepositoryException(
    //             "Failed to find active sales for region {$regionCode}: " . $th->getMessage(),
    //             $th->getCode(),
    //             $th,
    //         );
    //     }
    // }

    /**
     * Generate column aliases dynamically using QueryBuilder table aliases.
     */
    private function getColumnsWithDynamicAliases(string $tableName, array $columns): array
    {
        // Get the table alias that QueryBuilder will generate for this table
        $tableAlias = $this->getTableAliasForTable($tableName);

        $aliasedColumns = [];
        foreach ($columns as $column) {
            $aliasedColumns[] = "{$tableAlias}.{$column} AS {$tableAlias}_{$column}";
        }

        return $aliasedColumns;
    }

    /**
     * Predict or get the table alias that QueryBuilder will use
     * This should match the logic in your TablesAliasHelper.
     */
    private function getTableAliasForTable(string $tableName): string
    {
        $aliasMap = [
            'product' => 'u',  // Your QueryBuilder uses 'u' for product
            'region' => 'r',   // Your QueryBuilder uses 'r' for region
            'currency' => 'c',  // Your QueryBuilder uses 'c' for currency
        ];

        return $aliasMap[$tableName] ?? strtolower($tableName[0]);
    }
    // public function findActiveSales(string $regionCode): void
    // {
    //     try {
    //         if (empty($regionCode)) {
    //             throw new RepositoryInvalidArgumentException('Region code cannot be empty');
    //         }

    //         $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

    //         $qb = $this->em->createQueryBuilder()
    //             ->select()
    //             ->from('product_regional_price')
    //             ->leftJoin('product', self::PRODUCT_FILABLE)
    //             ->on('pdt_id', 'product.pdt_id')
    //             ->leftJoin('region', self::REGION_FILABLE)
    //             ->on('region_code', 'region.region_code')
    //             ->leftJoin('currency', self::CURRENCY_FILABLE)
    //             ->on(['currency_id' => 'currency.currency_id'])
    //             ->where(['region_code' => $regionCode])
    //             ->andWhere('is_active', true)
    //             ->andWhere(['sale_price', 'IS NOT', 'NULL'])
    //             ->and(function ($qb) use ($now) {
    //                 $qb->orWhere(['sale_start_date', 'IS', 'NULL'])
    //                    ->orWhere(['sale_start_date', '<=', $now]);
    //             })
    //             ->and(function ($qb) use ($now) {
    //                 $qb->orWhere(['sale_end_date', 'IS', 'NULL'])
    //                    ->orWhere(['sale_end_date', '>=', $now]);
    //             });

    //         $qb->build();
    //     } catch (RepositoryInvalidArgumentException $e) {
    //         throw $e;
    //     } catch (Throwable $th) {
    //         throw new RepositoryException(
    //             "Failed to find active sales for region {$regionCode}: " . $th->getMessage(),
    //             $th->getCode(),
    //             $th,
    //         );
    //     }
    // }

    /*
     * Convert entity to array for database operations.
     */
    // private function entityToArray(ProductRegionalPrice $entity): array
    // {
    //     return [
    //         'pdt_id' => $entity->getPdtId(),
    //         'region_code' => $entity->getRegionCode(),
    //         'base_price' => $entity->getBasePrice(),
    //         'compare_price' => $entity->getComparePrice(),
    //         'cost_price' => $entity->getCostPrice(),
    //         'sale_price' => $entity->getSalePrice(),
    //         'sale_start_date' => $entity->getSaleStartDate()?->format('Y-m-d H:i:s'),
    //         'sale_end_date' => $entity->getSaleEndDate()?->format('Y-m-d H:i:s'),
    //         'currency_id' => $entity->getCurrencyId(),
    //         'is_active' => $entity->getIsActive(),
    //         'updated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
    //     ];
    // }
}