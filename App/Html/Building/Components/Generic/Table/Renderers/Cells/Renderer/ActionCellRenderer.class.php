<?php

declare(strict_types=1);

final class ActionCellRenderer implements TableCellRendererInterface
{
    public function __construct(
        private readonly IconBuilder $icon,
    ) {
    }

    public function render(
        Mixed $entity,
        array $colDef,
        int $rowIndex,
        HtmlBuilder $builder,
    ): AbstractHtmlComponent {
        $entityId = isset($colDef['id']) ? ($colDef['id'])($entity) : '';
        $idField = $colDef['idField'] ?? 'public_id';

        /** @var ActionDefinition[] $actions */
        $actions = $colDef['actions'] ?? [];
        $actions = $actions instanceof Closure ? $actions($entity) : $actions;

        $actionForms = array_map(
            fn (ActionDefinition $action) => $this->buildAction(
                $action,
                $entityId,
                $idField,
                $builder,
            ),
            $actions,
        );

        return $builder->tag('td')
            ->class('table__body--row-cell', 'table__cell--action')
            ->add(
                $builder->tag('div')->class('body-cell-action')->add(
                    ...$actionForms,
                ),
            );
    }

    private function buildAction(
        ActionDefinition $action,
        mixed $entityId,
        string $idField,
        HtmlBuilder $builder,
    ): AbstractHtmlComponent {
        if (strtolower($action->method) === 'get') {
            return $builder->link()
                ->href($action->action)
                ->class('action-btn')
                ->add(
                    $this->icon->createIcon($action->icon, $action->iconLabel, $action->iconClasses),
                    $builder->tag('span')
                        ->class('visually-hidden')
                        ->content($action->screenReaderText),
                );
        }
        $button = $builder->button($action->buttonType)
            ->class('action-btn')
            ->add(
                $this->icon->createIcon($action->icon, $action->iconLabel, $action->iconClasses),
                $builder->tag('span')
                    ->class('visually-hidden')
                    ->content($action->screenReaderText),
            );

        if (!empty($action->buttonCustom)) {
            $button = $button->class('modal-open-btn')->custom($action->buttonCustom);
        }

        return $builder->form($action->csrfProtected)
            ->action($action->action)
            ->method($action->method)
            ->class('body-cell-action__form', $action->actionClass)
            ->add(
                $builder->input('hidden')->name($idField)->value($entityId),
                $button,
                $action->blockType !== null ? $builder->input('hidden')->name('block_type')->value($action->blockType) : null,
            );
    }
}