<?php

declare(strict_types=1);

class CacheUserCartItemListener implements EventListenerInterface
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
        $this->cacheUserItems($object);
        return $event;
    }

    private function cacheUserItems(LogoutController $logout): void
    {
        $userItems = $this->cartModel->getUserItem(
            $logout->getUserId()
        );
        if (empty($userItems)) {
            return;
        }
        $logout->getCache()->set('cart', $userItems);
    }
}