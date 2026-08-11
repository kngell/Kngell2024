<?php

declare(strict_types=1);

enum ShoppingCartKeys: string
{
    case LIST = 'cartList';
    case SUMMARY = 'cartSummary';
    case EMPTY = 'emptyCart';
}