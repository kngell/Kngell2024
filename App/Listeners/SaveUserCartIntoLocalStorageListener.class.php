<?php

declare(strict_types=1);

class SaveUserCartIntoLocalStorageListener implements EventListenerInterface
{
    public function __construct(private CartModel $cartModel)
    {
    }

    public function update(EventInterface $event): ?object
    {
        $object = $event->getObject();
        if (! $object instanceof LogoutController) {
            return new NullEvent();
        }
        $result = $this->exposeUserCartTobrowser($object);
        return $event->setResults($result);
    }

    private function exposeUserCartTobrowser(LogoutController $logout) : string
    {
        $userId = $logout->getUserId();
        $userItems = $this->cartModel->getUserItem($userId);

        $cartData = [];
        foreach ($userItems as $item) {
            $cartData[$item->getItemId()] = [
                'item_id' => $item->getItemId(),
                'item_quantity' => $item->getItemQuantity(),
                'item_name' => $item->getItemName(),
                'item_price' => $item->getItemPrice(),
                'media' => $item->getMedia(),
                'category_name' => $item->getCategoryName(),
            ];
        }
        $cookie = $logout->getCookie();
        if ($cookie->exists('user_cart')) {
            $cookie->delete('user_cart');
        }
        // Expose cart data to browser via a cookie (JSON encoded)
        if (! empty($cartData)) {
            return json_encode($cartData);
        }
        return '';
    }
}