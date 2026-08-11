<?php

declare(strict_types=1);

class FooterColumnSection extends AbstractBaseHtmlSection
{
    use EntityDisplayTrait;
    use FooterSectionHeaderTrait;
    private const string MODAL_TYPE = 'column';

    private string $entityClass = FooterMenuShow::class;

    #[Override]
    public function getKey(): string
    {
        return FooterSectionKeys::COLUMN->value;
    }

    public function getConfig(array $entities = [], array $pagination = []): array|AbstractHtmlComponent
    {
        $entities = $entities[$this->entityClass] ?? [];
        $pagination = $this->pagination[$this->entityClass] ?? [];
        $components = [
            $this->sectionHeader(
                title: 'Footer Menu Columns',
                action: 'add-column',
                type: self::MODAL_TYPE,
                url: '/admin/footer-column/add',
                textSubmit: 'Add Column',
            ),
            $this->sortableGrid($entities),
        ];
        if (!empty($pagination)) {
            $components[] = $this->htmlBuilder->htmlBlock($pagination);
        }
        return $components;
    }

    private function sortableGrid(array $entities): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $gridItems = [];
        if (ArrayUtils::isObjectList($entities) && $entities[0] instanceof Entity) {
            foreach ($entities as $index => $entity) {
                if ($entity === null) {
                    continue;
                }
                $gridItems = array_merge($gridItems, [$this->gridItem($entity, $index)]);
            }
        }

        return $html->div()->class('sortable-grid')->id('columns-grid')->add(
            ...$gridItems,
        );
    }

    /**
     * @param null|FooterMenuShow $entity
     * @param int $index
     *
     * @return null|AbstractHtmlComponent
     */
    private function gridItem(FooterMenuShow $entity, int $index = 0): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $gridItem = $html->div()
            ->class('grid-item')
            ->attribute('data-id', $this->show($entity, 'id'))
            ->attribute('data-sort', $index);
        $dragHandle = $html->div()->class('drag-handle')->add(
            $this->iconBuilder->createIcon('icon-drag', 'Drag', ['drag']),
        );
        $active = $html->tag('span')->class('status-badge');
        if ($entity->getIsActive()) {
            $active->class('active')->content('Active');
        } else {
            $active->class('inactive')->content('Inactive');
        }
        $itemDetails = $html->div()->class('item-details')->add(
            $html->tag('code')->content('key :' . $this->show($entity, 'title')),
            $html->tag('span')->content('Sort : ' . $index),
        );
        $itemContent = $html->div()->class('item-content')->add(
            $html->div()->class('item-header')->add(
                $html->tag('h3')->content($this->show($entity, 'title')),
                $active,
            ),
            $itemDetails,
        );

        return $gridItem->add(
            $dragHandle,
            $itemContent,
            $this->itemActions($entity),
        );
    }

    private function itemActions(FooterMenuShow $entity): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $entityId = $this->show($entity, 'id');

        $btnEdit = $this->wrapButtonWithform(
            action: '/admin/footer-column/edit',
            entityId: $entityId,
            components:[$this->button(
                id: $entityId,
                action: 'edit-column',
                icon: $this->iconBuilder->createIcon('icon-edit', 'Edit', ['edit']),
                class: ['icon-btn'],
            )],
        );

        $btnDelete = $this->wrapButtonWithForm(
            action: '/admin/footer-column-confirm-deletion/confirm',
            entityId: $entityId,
            components: [$this->button(
                id: $entityId,
                action: 'confirm-delete',
                icon: $this->iconBuilder->createIcon('icon-trash', 'Delete', ['delete']),
                class: ['icon-btn', 'delete'],
            )],
        );
        return $html->div()->class('item-actions')->add(
            $btnEdit,
            $btnDelete,
        );
    }

    private function button(int|string $id, string $action, AbstractHtmlComponent $icon, array $class): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $button = $html->button()
            ->class(...$class)
            ->custom([
                'data-action' => $action,
                'data-id' => (string) $id,
            ]);

        if ($action !== 'confirm-delete') {
            $button->attribute('data-modal-type', self::MODAL_TYPE);
        }
        return $button->add(
            $icon,
        );
    }
}