<?php

declare(strict_types=1);

enum PageWrapperSection: string
{
    case HEADER_TOP = 'headerTop';
    case HEADER_BOTTOM = 'headerBottom';
    case FOOTER = 'footer';
}