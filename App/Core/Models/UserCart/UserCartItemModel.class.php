<?php

declare(strict_types=1);

class UserCartItemModel extends Model
{
    public function deleteByCartId(int $cartId): QueryResult
    {
        return $this->delete(['cart_id' => $cartId]);
    }

    public function saveItems(array $items): QueryResult
    {
        return $this->save($items);
    }
}