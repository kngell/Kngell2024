<?php

declare(strict_types=1);

enum StockStatusCode: string
{
    case IN_STOCK = 'in_stock';
    case OUT_OF_STOCK = 'out_of_stock';
    case BACKORDERED = 'backordered';
    case PRE_ORDER = 'pre_order';
    case DISCONTINUED = 'discontinued';
}