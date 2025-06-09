<?php

declare(strict_types=1);

class MergeSessionAndDatabaseUserCartListener implements EventListenerInterface
{
    public function __construct(private CartModel $cartModel)
    {
    }

    public function update(EventInterface $event): ?object
    {
        $object = $event->getObject();
        if (! $object instanceof LoginController) {
            return new NullEvent();
        }
        $this->mergeSessionAndDatabaseCart($object);

        return new NullEvent();
    }

    private function mergeSessionAndDatabaseCart(LoginController $object): void
    {
        $userId = $object->getSession()->get(CURRENT_USER_SESSION_NAME);
        if ($userId === null) {
            return;
        }

        $cart = $this->cartModel->getCart($userId);
        // if (empty($cart)) {
        //     return;
        // }

        if ($object->getSession()->exists('cart')) {
            $userSessionCart = $object->getSession()->get('cart');
            foreach ($userSessionCart as $sessionItem) {
                $itemExistsInCart = false;
                foreach ($cart as $cartItem) {
                    if ($sessionItem['item_id'] === $cartItem->getItemId()) {
                        $itemExistsInCart = true;
                        $cartItem->setItemQuantity($cartItem->getItemQuantity() + $sessionItem['item_quantity']);
                        $cartItem->setItemName($sessionItem['item_name']);
                        $cartItem->setItemPrice($sessionItem['item_price']);
                        $this->cartModel->save($cartItem);
                        break;
                    }
                }
                if (! $itemExistsInCart) {
                    // Add new item from session to cart
                    $newCartItem = new Cart();
                    $newCartItem->setUserId($userId);
                    $newCartItem->setItemId($sessionItem['item_id']);
                    $newCartItem->setItemQuantity($sessionItem['item_quantity']);
                    $newCartItem->setItemName($sessionItem['item_name']);
                    $newCartItem->setItemPrice($sessionItem['item_price']);
                    $this->cartModel->save($newCartItem);
                }
            }
            // Clear the session cart after merging
            $object->getSession()->delete('cart');
        }
    }
}