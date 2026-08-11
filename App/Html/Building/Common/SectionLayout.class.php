<?php

declare(strict_types=1);

enum SectionLayout: string
{
    case LAYOUT_STANDARD = 'standard';
    case LAYOUT_TWO_COLUMNS = 'two-columns';
    case LAYOUT_CUSTOM_ROWS = 'custom-rows';
    case LAYOUT_CUSTOM = 'custom';
}