<?php

declare(strict_types=1);

interface UserCartHTMLInterface
{
    public function getCartItems() : string;

    public function getWhishlistItems() : string;

    public function getItems(): CollectionInterface;
}