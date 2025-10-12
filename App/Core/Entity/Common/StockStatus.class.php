<?php

declare(strict_types=1);




enum StockStatus: string
{
    case IN_STOCK    = 'in_stock';    // Ready to sell
    case OUT_OF_STOCK = 'out_of_stock'; // No stock
    case BACKORDER   = 'backorder';   // Can be ordered, ships later
    case PREORDER    = 'preorder';    // Not yet released, but available for early orders
}