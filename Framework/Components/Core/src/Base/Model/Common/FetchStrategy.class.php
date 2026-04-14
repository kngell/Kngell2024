<?php

declare(strict_types=1);

enum FetchStrategy: string
{
    case STANDARD = 'standard';
    case RELATIONSHIP_AWARE = 'relationship_aware';
    case ASSOCIATIVE = 'associative';
    case COLUMN = 'column';
    case KEY_PAIR = 'key_pair';
}
