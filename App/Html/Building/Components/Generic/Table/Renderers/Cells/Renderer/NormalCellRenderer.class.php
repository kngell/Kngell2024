<?php

declare(strict_types=1);

final class NormalCellRenderer implements TableCellRendererInterface
{
    public function render(
        Mixed $entity,
        array $colDef,
        int $rowIndex,
        HtmlBuilder $builder,
    ): AbstractHtmlComponent {
        $key = $colDef['key'];
        $value = isset($colDef['value']) ? ($colDef['value'])($entity) : '';
        $bodyCellModifierClass = $colDef['bodyCellModifierClass'] ?? null;

        // Build body-cell class list
        $cellClasses = ['body-cell'];
        if ($bodyCellModifierClass) {
            $cellClasses[] = "body-cell--{$bodyCellModifierClass}";
        }

        return $builder->tag('td')
            ->class('table__body--row-cell', 'table__cell--normal')
            ->add(
                $builder->tag('div')
                    ->class(...$cellClasses)
                    ->add(
                        $builder->tag('span')->content((string) $value),
                    ),
            );
    }
}