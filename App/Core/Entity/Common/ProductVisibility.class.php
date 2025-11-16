<?php

declare(strict_types=1);
enum ProductVisibility: string
{
    case VISIBLE = 'visible';
    case CATALOG = 'catalog';
    case SEARCH = 'search';
    case HIDDEN = 'hidden';
}