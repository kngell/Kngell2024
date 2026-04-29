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
        $colorModifier = $colDef['colorModifier'] ?? null;

        // Build body-cell class list
        $cellClasses = ['body-cell'];
        if ($colorModifier) {
            $cellClasses[] = "body-cell--{$colorModifier}";
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