<?php

declare(strict_types=1);

enum PathElementType : string
{
    case NORMAL = 'NORMAL';
    case VARIABLE = 'VARIABLE';
}