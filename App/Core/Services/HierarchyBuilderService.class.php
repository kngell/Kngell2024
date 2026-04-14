<?php

declare(strict_types=1);
class HierarchyBuilderService
{
    public static function buildIndentedList(
        array $items,
        string $labelGetter = 'getName',
        string $valueGetter = 'getId',
        string $levelGetter = 'getLevel',
        string $indentChar = '—',
    ): array {
        $options = [];
        foreach ($items as $item) {
            $level = (int) $item->$levelGetter();
            $indent = $level > 0 ? str_repeat($indentChar, $level) . ' ' : '';

            $options[$item->$valueGetter()] = $indent . $item->$labelGetter();
        }
        return $options;
    }
}