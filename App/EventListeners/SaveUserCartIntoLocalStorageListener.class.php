<?php

declare(strict_types=1);

// class SaveUserCartIntoLocalStorageListener implements EventListenerInterface
// {
//     public function __construct(private CartModel $cartModel)
//     {
//     }

//     public function update(EventInterface $event): ?object
//     {
//         $object = $event->getObject();
//         if (! $object instanceof LogoutController) {
//             return new NullEvent();
//         }
//         $result = $this->exposeUserCartTobrowser($object);
//         return $event->setResults($result);
//     }

//     private function exposeUserCartTobrowser(LogoutController $logout) : string
//     {
//         $userId = $logout->getUserId();
//         $userItems = $this->cartModel->getUserItem($userId);

//         $cartData = [];
//         foreach ($userItems as $item) {
//             $cartData[$item->getItemId()] = [
//                 'item_id' => $item->getItemId(),
//                 'item_quantity' => $item->getItemQuantity(),
//                 'item_name' => $item->getItemName(),
//                 'item_price' => $item->getItemPrice(),
//                 'media' => $item->getMedia(),
//                 'category_name' => $item->getCategoryName(),
//             ];
//         }
//         $cookie = $logout->getCookie();
//         if ($cookie->exists('user_cart')) {
//             $cookie->delete('user_cart');
//         }
//         // Expose cart data to browser via a cookie (JSON encoded)
//         if (! empty($cartData)) {
//             return json_encode($cartData);
//         }
//         return '';
//     }
// }

class SaveUserCartIntoLocalStorageListener implements EventListenerInterface
{
    // ... constructor remains the same

    public function update(EventInterface $event): bool
    {
        // 1. Type-Check and retrieve data from the payload (the event object itself)
        if (!$event instanceof UserLoggedOutEvent) {
            return false; // Skip if it's the wrong type of event
        }

        $userId = $event->getUserId();
        $cookie = $event->getCookie(); // Directly from the Event payload

        // 2. Execute the business logic
        $cartJson = $this->buildCartJson($userId);

        // 3. Perform side-effect (cookie manipulation)
        if ($cookie->exists('user_cart')) {
            $cookie->delete('user_cart');
        }
        if (!empty($cartJson)) {
            // Assuming the Cookie object has a method to set the JSON
            $cookie->set('user_cart', $cartJson);
        }

        // Return success/failure (use a boolean, not the modified event object)
        return true;
    }

    private function buildCartJson(int $userId): string
    {
        // Logic remains the same, but is isolated and clean.
        $userItems = $this->cartModel->getUserItem($userId);
        // ... build $cartData array ...

        return empty($cartData) ? '' : json_encode($cartData);
    }
}