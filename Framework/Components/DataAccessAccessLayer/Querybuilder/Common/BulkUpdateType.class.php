<?php

declare(strict_types=1);

enum BulkUpdateType: string
{
    case VALUES_CONSTRUCTOR = 'values';     // Fast, no WHERE support
    case SELECT_UNION_ALL = 'union_all';
    case TEMP_TABLE = 'temp';               // Slower, WITH WHERE support
    case AUTO = 'auto';                     // Auto-detect based on conditions
    case UPSERT = 'upsert';                 // Insert or update
    case BATCH = 'batch';                   // Multiple single updates in transaction
}
