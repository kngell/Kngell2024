<?php

declare(strict_types=1);

enum ProductStatus: string
{
    case DRAFT = 'draft';      // Not yet published
    case PUBLISHED = 'published';  // Visible to customers
    case ARCHIVED = 'archived';   // Old, hidden, but kept for records
    case ACTIVE = 'active';
}