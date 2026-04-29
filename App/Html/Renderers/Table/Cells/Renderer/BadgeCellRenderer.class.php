<?php

declare(strict_types=1);

final class BadgeCellRenderer implements TableCellRendererInterface
{
    public function render(
        Mixed $entity,
        array $colDef,
        int $rowIndex,
        HtmlBuilder $builder,
    ): AbstractHtmlComponent {
        $value = isset($colDef['value']) ? ($colDef['value'])($entity) : '';
        $badgeClasses = $colDef['badgeClasses'] ?? ['badge', 'badge--warning'];

        return $builder->tag('td')
            ->class('table__body--row-cell', 'table__cell--badge')
            ->add(
                $builder->tag('div')
                    ->class('body-cell-badge')
                    ->add(
                        $builder->tag('span')
                            ->class(...$badgeClasses)
                            ->content((string) $value),
                    ),
            );
    }
}