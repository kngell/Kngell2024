<?php

declare(strict_types=1);

final class TableConfig
{
    public function __construct(
        public string $entityKey,
        public array $jsAttributes,
        public string $captionText,
        public string $expectedControllerClass,
        public array $columns,
    ) {
        if ($columns === []) {
            throw new InvalidArgumentException('TableConfig requires at least one column.');
        }

        $seenKeys = [];
        foreach ($columns as $i => $col) {
            if (!$col instanceof TableColumn) {
                throw new InvalidArgumentException("Column #{$i} is not a TableColumn.");
            }
            if (isset($seenKeys[$col->key])) {
                throw new InvalidArgumentException("Duplicate column key: '{$col->key}'.");
            }
            $seenKeys[$col->key] = true;
        }
    }

    /** @return array<int, array<string, mixed>> shape consumed by TableHeadSection */
    public function headRows(): array
    {
        return array_map(fn (TableColumn $c) => array_filter([
            'key' => $c->key,
            'label' => $c->label,
            'cellType' => $c->cellType,
            'sortable' => $c->sortable,
            'hasCheckbox' => $c->hasCheckbox,
            'hasDropdown' => $c->hasDropdown,
            'hintText' => $c->hintText,
            'ariaLabel' => $c->ariaLabel,
        ], static fn ($v) => $v !== null && $v !== false && $v !== ''), $this->columns);
    }

    /** @return array<int, array<string, mixed>> shape consumed by TableBodySection */
    public function bodyRows(): array
    {
        return array_map(function (TableColumn $c) {
            $row = ['key' => $c->key, 'cellType' => $c->cellType];

            if ($c->bodyCellModifierClass !== null) {
                $row['bodyCellModifierClass'] = $c->bodyCellModifierClass;
            }
            if ($c->badgeClasses !== []) {
                $row['badgeClasses'] = $c->badgeClasses;
            }

            if ($c->cellType === TableCellType::ACTION) {
                $row['idField'] = $c->idField;
                $row['id'] = $c->idValue;
                $row['actions'] = $c->actionsBuilder;
                return $row;
            }

            if ($c->checkboxName !== null) {
                $row['checkboxName'] = $c->checkboxName;
                $row['thumbnail'] = $c->thumbnail;
                $row['thumbnailAlt'] = $c->thumbnailAlt;
                $row['title'] = $c->title;
                $row['subtitle'] = $c->subtitle;
                return $row;
            }

            $row['value'] = $c->value;
            return $row;
        }, $this->columns);
    }

    /** @return string[] one BEM modifier per column */
    public function colGroupClasses(): array
    {
        return array_map(fn (TableColumn $c) => $c->colClass, $this->columns);
    }
}