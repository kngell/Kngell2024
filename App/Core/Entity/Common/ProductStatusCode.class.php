<?php

declare(strict_types=1);

enum ProductStatusCode: string
{
    public function getDisplayName(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::ARCHIVED => 'Archived',
            self::DISCONTINUED => 'Discontinued',
            self::PRE_ORDER => 'Pre-Order',
            self::BACKORDERED => 'Backordered',
            self::INACTIVE => 'Inactive',
            self::PENDING_REVIEW => 'Pending Review',
            self::SEASONAL => 'Seasonal',
            self::COMING_SOON => 'Coming Soon',
        };
    }

    public function isAvailableForPurchase(): bool
    {
        return match($this) {
            self::ACTIVE, self::PRE_ORDER, self::BACKORDERED => true,
            default => false,
        };
    }

    public function isVisibleInCatalog(): bool
    {
        return match($this) {
            self::ACTIVE, self::PRE_ORDER, self::BACKORDERED,
            self::COMING_SOON, self::SEASONAL => true,
            default => false,
        };
    }

    public function allowsInventoryManagement(): bool
    {
        return match($this) {
            self::DRAFT, self::ARCHIVED, self::DISCONTINUED,
            self::INACTIVE => false,
            default => true,
        };
    }

    public function getSortOrder(): int
    {
        return match($this) {
            self::ACTIVE => 10,
            self::PRE_ORDER => 20,
            self::BACKORDERED => 30,
            self::COMING_SOON => 40,
            self::SEASONAL => 50,
            self::PENDING_REVIEW => 60,
            self::DRAFT => 70,
            self::INACTIVE => 80,
            self::ARCHIVED => 90,
            self::DISCONTINUED => 100,
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::DRAFT => 'Product is in draft mode, not visible to customers',
            self::ACTIVE => 'Product is active and available for purchase',
            self::ARCHIVED => 'Product is archived and hidden from catalog',
            self::DISCONTINUED => 'Product is no longer manufactured/sold',
            self::PRE_ORDER => 'Available for pre-order before release date',
            self::BACKORDERED => 'Temporarily out of stock but can be ordered',
            self::INACTIVE => 'Product is temporarily unavailable',
            self::PENDING_REVIEW => 'Awaiting approval before going live',
            self::SEASONAL => 'Seasonal product available during specific periods',
            self::COMING_SOON => 'Product will be available soon',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::DRAFT => '#6B7280', // gray
            self::ACTIVE => '#10B981', // green
            self::ARCHIVED => '#6B7280', // gray
            self::DISCONTINUED => '#EF4444', // red
            self::PRE_ORDER => '#F59E0B', // amber
            self::BACKORDERED => '#F59E0B', // amber
            self::INACTIVE => '#6B7280', // gray
            self::PENDING_REVIEW => '#F59E0B', // amber
            self::SEASONAL => '#8B5CF6', // violet
            self::COMING_SOON => '#3B82F6', // blue
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::DRAFT => '📝',
            self::ACTIVE => '✅',
            self::ARCHIVED => '📦',
            self::DISCONTINUED => '❌',
            self::PRE_ORDER => '⏰',
            self::BACKORDERED => '📥',
            self::INACTIVE => '⏸️',
            self::PENDING_REVIEW => '👀',
            self::SEASONAL => '🎄',
            self::COMING_SOON => '🚀',
        };
    }

    public static function getActiveStatuses(): array
    {
        return [
            self::ACTIVE,
            self::PRE_ORDER,
            self::BACKORDERED,
        ];
    }

    public static function getInactiveStatuses(): array
    {
        return [
            self::DRAFT,
            self::INACTIVE,
            self::ARCHIVED,
            self::DISCONTINUED,
            self::PENDING_REVIEW,
        ];
    }

    public static function getPurchasableStatuses(): array
    {
        return [
            self::ACTIVE,
            self::PRE_ORDER,
            self::BACKORDERED,
        ];
    }
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case DISCONTINUED = 'discontinued';
    case PRE_ORDER = 'pre_order';
    case BACKORDERED = 'backordered';
    case INACTIVE = 'inactive';
    case PENDING_REVIEW = 'pending_review';
    case SEASONAL = 'seasonal';
    case COMING_SOON = 'coming_soon';
}