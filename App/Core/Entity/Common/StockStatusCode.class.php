<?php

declare(strict_types=1);

enum StockStatusCode: string
{
    case IN = 'in_stock';
    case OUT = 'out_of_stock';
}