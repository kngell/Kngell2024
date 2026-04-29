<?php

declare(strict_types=1);

enum TableListSection: string
{
    case CAPTION = 'caption';
    case COL_GROUP = 'colGroup';
    case THEAD = 'thead';
    case TBODY = 'tbody';
    case TFOOT = 'tfoot';
}