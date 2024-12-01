<?php

declare(strict_types=1);

class UserCartHTMLElement implements UserCartHTMLInterface
{
    private CollectionInterface $items;

    public function __construct(private UserCartItems $userCart, private UserCartItemsForm $form)
    {
        $this->items = $userCart->get();
    }

    public function getCartItems() : string
    {
        return $this->form->createForm('#', $this->items);
    }

    public function getWhishlistItems() : string
    {
        return $this->whishlist();
    }

    /**
     * Get the value of items.
     */
    public function getItems(): CollectionInterface
    {
        return $this->items;
    }

    /**
     * Set the value of items.
     */
    public function setItems(CollectionInterface $items): self
    {
        $this->items = $items;

        return $this;
    }

    private function whishlist() : string
    {
        $whislist = $this->items->filter(function ($item) {
            return $item->cartType === 'wishlist';
        });

        return '<a href="/cart#wishlist" class="px-3 border-right text-dark text-decoration-none" id="wishlist_items_count">Whishlist(' . ($whislist->count() > 0 ? $whislist->count() : 0) . ')</a>';
    }
}