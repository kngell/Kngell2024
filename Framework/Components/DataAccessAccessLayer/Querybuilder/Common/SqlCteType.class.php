<?php

declare(strict_types=1);

enum SqlCteType: string
{
    case STANDALONE = 'standalone';
    case NESTED = 'nested';
    case RECURSIVE = 'recursive';
}