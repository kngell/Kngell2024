<?php

declare(strict_types=1);

final class CartWriteOperationsService
{
    private readonly EntityManagerInterface $em;

    public function __construct(
        private readonly UserCartModel $cartModel,
        private readonly UserCartItemModel $itemModel,
        private readonly UserCartShowModel $cartShowModel,
    ) {
        $this->em = $cartModel->getEntityManager();
    }

    public function saveCart(?int $userId, string $sessionId, array $cartItems, ?int $cartPk = null): CartOperationResult
    {
        $this->em->beginTransaction();

        try {
            $cartData = [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($cartPk !== null) {
                $cartData['uc_id'] = $cartPk;
            } else {
                $cartData['created_at'] = date('Y-m-d H:i:s');
            }
            $result = $this->cartModel->save($cartData);
            $operation = strtolower($result->getSqlOperation()->value) ?? 'unknown';

            if (!$result->isSuccess()) {
                $this->em->rollBack();
                return CartOperationResult::failure(
                    operation: $operation,
                    error: 'Failed to save cart',
                    data: ['sql_error' => $result->getSkipReason()],
                );
            }
            $cartId = $result->getLastInsertId();
            if ($result->getSqlOperation()->value === 'UPDATE') {
                $cartId = $result->getLastUpdateId();
            }

            if (!$cartId) {
                $this->em->rollBack();
                return CartOperationResult::failure(
                    operation: $operation,
                    error: 'Could not determine cart ID',
                );
            }
            $delResult = $this->itemModel->delete(['cart_id' => $cartId]);
            if (!$delResult->isSuccess()) {
                $this->em->rollBack();
                return CartOperationResult::failure(
                    operation: 'delete',
                    error: 'Could not delete cart items',
                );
            }

            $insertedCount = 0;
            if (!empty($cartItems)) {
                $cartRows = [];
                foreach ($cartItems as $item) {
                    $cartRows[] = [
                        'cart_id' => $cartId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'variant_data' => json_encode($item['variant_data'] ?? null),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }

                $itemResult = $this->itemModel->save($cartRows);
                if (!$itemResult->isSuccess()) {
                    $this->em->rollBack();
                    return CartOperationResult::failure(
                        operation: 'insert',
                        error: 'Failed to save cart items',
                        data: ['sql_error' => $itemResult->getSkipReason()],
                    );
                }
                $insertedCount = $itemResult->getAffectedRows();
            }

            $this->em->commit();

            return CartOperationResult::saveSuccess(
                cartId: (int) $cartId,
                data: [
                    'items_saved' => $insertedCount,
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'was_new' => $cartPk === null,
                ],
                affectedRows: $result->getAffectedRows() + $insertedCount,
            );
        } catch (Exception $e) {
            $this->em->rollBack();
            return CartOperationResult::failure(
                operation: 'save',
                error: 'Cart save failed: ' . $e->getMessage(),
                data: ['exception' => $e->getMessage()],
            );
        }
    }

    public function deleteCart(int $cartPk): CartOperationResult
    {
        $this->em->beginTransaction();

        try {
            $result = $this->cartModel->delete(['uc_id' => $cartPk]);

            if (!$result->isSuccess()) {
                $this->em->rollBack();
                return CartOperationResult::failure(
                    operation: 'delete',
                    error: 'Failed to delete cart',
                    data: ['sql_error' => $result->getSkipReason()],
                );
            }

            $this->em->commit();

            return CartOperationResult::deleteSuccess(
                cartId: $cartPk,
                data: [
                    'items_deleted_automatically' => true,
                ],
                affectedRows: $result->getAffectedRows(),
            );
        } catch (Exception $e) {
            $this->em->rollBack();
            return CartOperationResult::failure(
                operation: 'delete',
                error: 'Cart delete failed: ' . $e->getMessage(),
                data: ['exception' => $e->getMessage()],
            );
        }
    }

    public function mergeCarts(int $userId, string $sessionId, int $guestCartPk, ?int $userCartPk = null): CartOperationResult
    {
        $this->em->beginTransaction();

        try {
            // Get items from both carts
            $userCart = $userCartPk ? $this->cartShowModel->findByCartId($userCartPk) : null;
            $guestCart = $this->cartShowModel->findByCartId($guestCartPk);

            if (!$guestCart) {
                $this->em->commit();
                return CartOperationResult::skipped(
                    operation: 'merge',
                    message: 'Guest cart not found',
                    data: ['guest_cart_id' => $guestCartPk],
                );
            }

            $userItems = $userCart ? $userCart->getUserCartItem() : [];
            $guestItems = $guestCart->getUserCartItem();

            $mergedItems = $this->mergeItemArrays($userItems, $guestItems);

            // Delete guest cart
            $this->cartModel->delete(['uc_id' => $guestCartPk]);

            // Save merged cart
            $cartId = $this->saveMergedCart($userId, $sessionId, $userCartPk, $mergedItems);

            $this->em->commit();

            $totalItems = array_sum(array_column($mergedItems, 'quantity'));

            return CartOperationResult::mergeSuccess(
                cartId: $cartId,
                data: [
                    'user_id' => $userId,
                    'guest_cart_id' => $guestCartPk,
                    'merged_items_count' => count($mergedItems),
                    'total_items' => $totalItems,
                ],
                affectedRows: count($mergedItems),
            );
        } catch (Exception $e) {
            $this->em->rollBack();
            return CartOperationResult::failure(
                operation: 'merge',
                error: 'Cart merge failed: ' . $e->getMessage(),
                data: ['exception' => $e->getMessage()],
            );
        }
    }

    public function clearCart(int $cartPk): CartOperationResult
    {
        $this->em->beginTransaction();

        try {
            // Delete all items
            $result = $this->itemModel->delete(['cart_id' => $cartPk]);
            if (!$result->isSuccess()) {
                $this->em->rollBack();
                return CartOperationResult::failure(
                    operation: 'clear',
                    error: 'Failed to clear cart items',
                    data: ['sql_error' => $result->getSkipReason()],
                );
            }

            // Update cart timestamp
            $this->cartModel->save([
                'uc_id' => $cartPk,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->em->commit();

            return CartOperationResult::clearSuccess(
                cartId: $cartPk,
                data: [
                    'items_cleared' => $result->getAffectedRows(),
                ],
                affectedRows: $result->getAffectedRows(),
            );
        } catch (Exception $e) {
            $this->em->rollBack();
            return CartOperationResult::failure(
                operation: 'clear',
                error: 'Cart clear failed: ' . $e->getMessage(),
                data: ['exception' => $e->getMessage()],
            );
        }
    }

    // ─── Private Methods ──────────────────────────────────────────

    private function mergeItemArrays(array $userItems, array $guestItems): array
    {
        $merged = [];

        foreach ($userItems as $item) {
            $merged[$item->getProductId()] = [
                'product_id' => $item->getProductId(),
                'quantity' => $item->getQuantity(),
                'variant_data' => $item->getVariantData(),
            ];
        }

        foreach ($guestItems as $guestItem) {
            $productId = $guestItem->getProductId();
            if (isset($merged[$productId])) {
                $merged[$productId]['quantity'] += $guestItem->getQuantity();
            } else {
                $merged[$productId] = [
                    'product_id' => $productId,
                    'quantity' => $guestItem->getQuantity(),
                    'variant_data' => $guestItem->getVariantData(),
                ];
            }
        }

        return $merged;
    }

    private function saveMergedCart(
        int $userId,
        string $sessionId,
        ?int $userCartPk,
        array $mergedItems,
    ): int {
        $cartData = [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($userCartPk !== null) {
            // Update existing user cart
            $cartData['uc_id'] = $userCartPk;
            $cartId = $userCartPk;

            $result = $this->cartModel->save($cartData);
            if (!$result->isSuccess()) {
                throw new RuntimeException('Failed to update user cart');
            }

            $this->itemModel->delete(['cart_id' => $cartId]);
        } else {
            // Create new user cart
            $cartData['created_at'] = date('Y-m-d H:i:s');
            $result = $this->cartModel->save($cartData);
            if (!$result->isSuccess()) {
                throw new RuntimeException('Failed to create user cart');
            }
            $cartId = $result->getLastInsertId();
        }

        if (!empty($mergedItems)) {
            $this->saveCartItems($cartId, $mergedItems);
        }

        return $cartId;
    }

    private function saveCartItems(int $cartId, array $items): void
    {
        $cartRows = [];
        foreach (array_values($items) as $item) {
            $cartRows[] = [
                'cart_id' => $cartId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'variant_data' => json_encode($item['variant_data'] ?? null),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $result = $this->itemModel->save($cartRows);
        if (!$result->isSuccess()) {
            throw new RuntimeException('Failed to save cart items');
        }
    }
}