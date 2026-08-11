<?php

declare(strict_types=1);

class FooterLinkSection extends AbstractBaseHtmlSection
{
    use EntityDisplayTrait;
    use FooterSectionHeaderTrait;
    private const string MODAL_TYPE = 'link';

    /** @var FooterMenuShow[] */
    private array $entities = [];

    private string $entityClass = FooterMenuShow::class;

    public function __construct(HtmlBuilder $htmlBuilder, IconBuilder $iconBuilder, private ButtonBuilder $buttonBuilder)
    {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    #[Override]
    public function getConfig(array $entities = []): array|AbstractHtmlComponent
    {
        $this->entities = $entities[$this->entityClass] ?? [];
        return [
            FormFieldConfig::create(
                name: 'column-filter',
                type: 'select',
            )->setId('column-filter')
            ->setClass(['filter-select'])
            ->setOptions($this->getOptions())
            ->setRightIcon([
                'icon' => 'icon-arrow-down',
                'aria' => 'Dropdown arrow',
            ]),
        ];
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        return [
            $this->header($fields),
            $this->menuLinksContent(),
        ];
    }

    #[Override]
    public function getKey(): string
    {
        return FooterSectionKeys::LINK->value;
    }

    private function header(array $fields): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()->class('section-header')->add(
            $html->tag('h2')->content('Footer Menu Links'),
            $this->filterGroup($fields),
        );
    }

    /**
     * @return AbstractHtmlComponent
     */
    private function filterGroup(array $fields): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $headerButton = $this->buttonBuilder->add(
            type: 'submit',
            label: 'Add Link',
            attributes:[
                'data-action' => 'add-link',
                'data-modal-type' => self::MODAL_TYPE,
            ],
            buttonStyle:'secondary',
            buttonClass: ['add-link'],
        )->build();
        return $this->wrapButtonWithform(
            action:'/admin/footer-link/add',
            classes: ['filter-group'],
            components: array_merge($fields, [$headerButton]),
        );
    }

    private function getOptions(): array
    {
        $entities = $this->entities;
        $options = ['all' => 'All Columns'];
        foreach ($entities as $entity) {
            $options[$this->show($entity, 'id')] = $this->show($entity, 'title');
        }
        return $options;
    }

    /**
     * @return AbstractHtmlComponent
     */
    private function menuLinksContent(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $entities = $this->entities;
        $columnGroup = [];
        foreach ($entities as $entity) {
            $columnGroup = array_merge($columnGroup, [$this->columnGroup($entity)]);
        }
        return $html->div()->class('links-container')->id('links-container')->add(
            ...$columnGroup,
        );
    }

    private function columnGroup(FooterMenuShow $entity): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $menuLinks = $entity->getFooterMenuLink();
        $id = $this->show($entity, 'id');

        $columnGroupHeader = $html->div()->class('column-group-header')->add(
            $html->tag('h3')->content($this->show($entity, 'title')),
            $html->tag('span')->class('link-count')->content(count($menuLinks) . ' links'),
        );
        $sortableList = $this->sortableList($id, $menuLinks, $entity);
        return $html->div()->class('column-group')
        ->attribute('data-column-id', $id)
        ->add(
            $columnGroupHeader,
            $sortableList,
        );
    }

    private function sortableList(int|string $id, array $menuLinks, FooterMenuShow $parentEntity): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $listItems = [];
        foreach ($menuLinks as $index => $menuLink) {
            $listItems = array_merge($listItems, [$this->listItem($menuLink, $parentEntity, $index)]);
        }
        return $html->div()->class('sortable-list')
        ->attribute('data-column', $id)
        ->add(
            ...$listItems,
        );
    }

    private function listItem(FooterMenuLink $entity, FooterMenuShow $parentEntity, int $index): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $listItemContainer = $html->div()->class('list-item')->custom(
            [
                'data-id' => $this->show($entity, 'id'),
                'data-sort' => $index,
            ],
        );
        $drag = $html->div()->class('drag-handle')->add(
            $this->iconBuilder->createIcon('icon-drag', 'Drag', ['drag']),
        );
        $itemInfo = $html->div()->class('item-info')->add(
            $html->tag('strong')->content($this->show($entity, 'title')),
            $html->tag('code')->content($this->show($entity, 'url')),
            $html->tag('span')->class('target-badge')->content('_self'),
        );
        $statuscontainer = $html->div()->class('item-status');
        $status = $html->tag('span')->class('status-badge');
        if ($entity->getIsActive()) {
            $status->class('active');
        }
        $statuscontainer->add($status->content('Active Status'));
        $actions = $this->actions($entity, $parentEntity);

        return $listItemContainer->add(
            $drag,
            $itemInfo,
            $statuscontainer,
            $actions,
        );
    }

    private function actions(FooterMenuLink $entity, FooterMenuShow $parentEntity): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $entityId = $this->show($entity, 'id');
        $editBtn = $this->buttonBuilder->iconOnly(
            new IconConfig(
                icon:'icon-edit',
                ariaLabel: 'Edit',
                iconClass: ['icon-btn'],
            ),
        )->build(
            new ButtonConfig(
                type: 'submit',
                attributes: [
                    'data-action' => 'edit-link',
                    'data-id' => $entityId,
                    'data-modal-type' => self::MODAL_TYPE,
                ],
            ),
        );
        $deleteBtn = $this->buttonBuilder->iconOnly(
            new IconConfig(
                icon:'icon-trash',
                ariaLabel: 'Delete',
                iconClass: ['icon-btn'],
            ),
        )->build(
            new ButtonConfig(
                type: 'submit',
                attributes: [
                    'data-action' => 'delete-link',
                    'data-id' => $entityId,
                    'data-modal-type' => self::MODAL_TYPE,
                ],
            ),
        );
        $parentInput = $html->input('hidden')->name('column_id')->value($this->show($parentEntity, 'id'));
        return $html->div()->class('item-actions')->add(
            $this->wrapButtonWithform(
                action: '/admin/footer-link/edit',
                entityId: $entityId,
                components:[
                    $editBtn,
                ],
            ),
            $this->wrapButtonWithform(
                action: '/admin/footer-link-confirm-deletion/confirm',
                entityId: $entityId,
                components:[
                    $parentInput,
                    $deleteBtn,
                ],
            ),
        );
    }
}