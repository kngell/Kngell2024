<?php

declare(strict_types=1);

class UserCartShowRepository extends Repository
{
    protected const array COLUMN_MAPS = [
        'user_cart' => [
            'uc_id', 'user_id', 'session_id', 'created_at', 'updated_at', 'expires_at',
        ],
        'user_cart_item' => [
            'cart_item_id', 'cart_id', 'product_id', 'quantity', 'variant_data', 'created_at', 'updated_at',
        ],
    ];

    public function findBy(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        try {
            $qb = $this->em->createQueryBuilder();
            $select = $qb->select($this->getAllColumns())
                ->from('user_cart')
                ->leftJoin('user_cart_item')
                ->on('uc_id', 'user_cart_item.cart_id');

            $sort = $this->extractOrderBy($conditions);

            if (!empty($conditions)) {
                $this->applyMixedConditions($select, $conditions);
            }

            if (!empty($sort)) {
                $this->applySqlKeywords($sort, $select);
            }

            if ($limit !== null) {
                $select->limit($limit);
            }

            if ($offset !== null) {
                $select->offset($offset);
            }

            $select->build();
            $this->debugSql($qb, 'json');
        } catch (Throwable $th) {
            throw new RepositoryException('Failed to find cart: ' . $th->getMessage(), 0, $th);
        }
    }

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        try {
            $qb = $this->em->createQueryBuilder();
            $select = $qb->selectWithAlias($this->getAllColumns())
                ->from('user_cart')
                ->leftJoin('user_cart_item')
                ->on('uc_id', 'user_cart_item.cart_id');

            // Extract ORDER BY before applying conditions
            $sort = $this->extractOrderBy($conditions);

            if (!empty($conditions)) {
                $this->applyMixedConditions($select, $conditions);
            }

            if (!empty($sort)) {
                $this->applySqlKeywords($sort, $select);
            }

            $select->build();
            // $this->debugSql($qb, 'json');
        } catch (Throwable $th) {
            throw new RepositoryException('Failed to find one cart: ' . $th->getMessage(), 0, $th);
        }
    }

    public function count(array $conditions = []): void
    {
        try {
            $qb = $this->em->createQueryBuilder();
            $select = $qb->select(['COUNT(user_cart.uc_id) as total'])
                ->from('user_cart');

            if (!empty($conditions)) {
                $this->applyMixedConditions($select, $conditions);
            }

            $select->build();
            // $this->debugSql($qb);
        } catch (Throwable $th) {
            throw new RepositoryException('Failed to count carts: ' . $th->getMessage(), 0, $th);
        }
    }
}