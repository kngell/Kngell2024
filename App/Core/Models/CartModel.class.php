<?php

declare(strict_types=1);

class CartModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    /**
     * @param int $userId
     * @return Cart[]
     */
    public function getCart(int $userId) : array
    {
        $cart = $this->all([
            'user_id' => $userId,
        ]);
        if ($cart->isEmpty()) {
            return [];
        }
        return $cart->getResults('class')->all();
    }

    public function getProduct(int|string $id) : Product|NullObjectInterface
    {
        $this->entityManager->createQueryBuilder()
            ->select()
            ->from('product')
            ->where('product.id', $id)
            ->build();
        $product = $this->entityManager->persist()->getResults();
        if ($product->isEmpty()) {
            return new NullObject();
        }
        return $product->getResults('class', Product::class)->first();
    }

    public function getUserItemQty(int $userId): int
    {
        $cart = $this->getCart($userId);
        if (empty($cart)) {
            return 0;
        }
        return array_reduce($cart, function ($carry, Cart $item) {
            return $carry + $item->getItemQuantity();
        }, 0);
    }

    /**
     * @param int $userId
     * @return Cart[]
     * @throws ValueError
     * @throws TypeError
     * @throws PDOException
     */
    public function getUserItem(int $userId): array
    {
        $this->entityManager->createQueryBuilder()
            ->select('cart_id', 'COUNT(cart.cart_id) AS number_of_items', 'SUM(cart.item_quantity) AS item_quantity', 'item_id')
            ->leftJoin('product', 'name AS item_name', 'price AS item_price', 'media', 'description', 'sku AS item_sku')
            ->on('product.id', 'cart.item_id')
            ->leftJoin('product_category')
            ->on('product_category.product_id', 'product.id')
            ->leftJoin('categories', 'category_name')
            ->on('categories.cat_id', 'product_category.category_id')
            ->leftJoin('brand', 'brand_name')
            ->on('brand.br_id', 'categories.br_id')
            ->where('cart.user_id', '=', $userId)
            ->groupBy('cart.item_id')
            ->build();
        $cart = $this->entityManager->persist()->getResults();
        if ($cart->isEmpty()) {
            return [];
        }
        $r = $cart->rowCount();
        return $cart->getResults('class')->all();
    }

    public function removeItemFromCart(string $id) : bool|NullObjectInterface
    {
        $delete = $this->delete(['item_id' => $id]);
        if ($delete->rowCount()) {
            return true;
        }
        return new NullObject;
    }
}