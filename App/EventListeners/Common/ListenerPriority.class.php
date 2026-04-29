<?php

declare(strict_types=1);

// In a constants class or enum
final class ListenerPriority
{
    // Data operations
    public const DATA_CRITICAL = 0;    // Backup, audit
    public const DATA_HIGH = 10;   // Core deletions/saves
    public const DATA_NORMAL = 20;   // Secondary data cleanup

    // Infrastructure
    public const CACHE = 50;   // Cache invalidation
    public const SEARCH_INDEX = 60;   // Search reindexing
    public const CDN = 70;   // CDN purge

    // External / async-safe
    public const NOTIFICATION = 90;   // Emails, alerts
    public const ANALYTICS = 100;  // Tracking, logging
}