<?php

declare(strict_types=1);

final class TableColumn
{
    public function __construct(
        // ─── Identity (shared by head, body, colgroup) ───
        public readonly string $key,
        public readonly TableCellType $cellType,
        public readonly string $colClass,                       // 'table__col--normal'

        // ─── Head-only ───
        public readonly ?string $label = null,
        public readonly bool $sortable = false,
        public readonly bool $hasCheckbox = false,
        public readonly bool $hasDropdown = false,
        public readonly ?string $hintText = null,
        public readonly ?string $ariaLabel = null,

        // ─── Body-only: simple cells ───
        public readonly ?Closure $value = null,                 // fn($entity) => string

        // ─── Body-only: compound 'select' cell ───
        public readonly ?string $checkboxName = null,
        public readonly ?Closure $thumbnail = null,
        public readonly ?Closure $thumbnailAlt = null,
        public readonly ?Closure $title = null,
        public readonly ?Closure $subtitle = null,

        // ─── Body-only: action cell ───
        public readonly ?string $idField = null,
        public readonly ?Closure $idValue = null,
        public readonly ?Closure $actionsBuilder = null,        // fn($entity) => ActionDefinition[]

        // ─── Body: cell presentation ───
        public readonly ?string $bodyCellModifierClass = null,  // NEW
        public readonly array $badgeClasses = [],
        public readonly ?string $blockType = null,           // NEW
    ) {
    }
}