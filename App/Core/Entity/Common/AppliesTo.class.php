<?php

declare(strict_types=1);

enum AppliesTo: string
{
    case ALL = 'all';
    case GOODS = 'goods';
    case SERVICE = 'services';
    case DIGITAL = 'digital';
}