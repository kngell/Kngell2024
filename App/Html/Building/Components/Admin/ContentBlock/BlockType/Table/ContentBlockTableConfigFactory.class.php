<?php

declare(strict_types=1);

final class ContentBlockTableConfigFactory extends AbstractTableConfigFactory
{
    use EntityDisplayTrait;

    public function __construct(
        HtmlSectionPresentationService $presenter,
        private readonly string $type,
    ) {
        parent::__construct($presenter);
    }

    #[Override]
    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: EntityKey::CONTENT_BLOCK->value . '_' . $this->type,
            displayName: $this->displayName($this->type),
            plural: 'content-blocks',
            basePath: '/admin/content-block-page',
            blockType: $this->type,
        );
    }

    #[Override]
    protected function columns(): array
    {
        $e = $this->entityDescriptor();
        $placeholder = $this->emptyPlaceholder();
        return [
            new TableColumn(
                key: 'select',
                cellType: TableCellType::START,
                colClass: 'table__col--start',
                label: 'Section',
                hasCheckbox: true,
                hasDropdown: true,
                ariaLabel: 'Select all sections',
                checkboxName: $e->checkboxName(),
                thumbnail: fn (ContentBlock|ContentBlockShow $c): string => $this->getImage($c, 'url'),
                thumbnailAlt: fn (ContentBlock|ContentBlockShow $c): string => $this->getImage($c, 'url') ?? 'Content Block image',
                title:fn (ContentBlock|ContentBlockShow $c): string => $this->show($c, 'title') ?: $placeholder,
                subtitle: fn (ContentBlock|ContentBlockShow $c): string => $this->show($c, 'subtitle') ?: $placeholder,
            ),
            new TableColumn(
                key: 'sort_order',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Order',
                sortable: true,
                hasDropdown: true,
                value: fn (ContentBlock|ContentBlockShow $c) => $this->show($c, 'sort_order') ?: $placeholder,
            ),
            new TableColumn(
                key: 'subtitle',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Subtitle',
                value: fn (ContentBlock|ContentBlockShow $c) => $this->show($c, 'subtitle') ?: $placeholder,
            ),
            new TableColumn(
                key: 'position',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Position',
                value: fn (ContentBlock|ContentBlockShow $c) => $this->show($c, 'position') ?: $placeholder,
            ),

            new TableColumn(
                key: 'cta_text',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Button Text',
                value: fn (ContentBlock|ContentBlockShow $c) => $this->show($c, 'button_text') ?: $placeholder,
            ),
            new TableColumn(
                key: 'cta_link',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--badge',   // ← matches your current colgroup
                label: 'Button Link',
                value: fn (ContentBlock|ContentBlockShow $c) => $this->show($c, 'button_link') ?: $placeholder,
            ),
            new TableColumn(
                key: 'action',
                cellType: TableCellType::ACTION,
                colClass: 'table__col--action',
                label: 'Action',
                ariaLabel: 'Actions',
                idField: 'id',
                idValue: fn (ContentBlock|ContentBlockShow $c): string => $this->show($c, 'id'),
                blockType: $this->type,
                actionsBuilder: fn (ContentBlock|ContentBlockShow $c): array => $this->blockActions($c),
            ),
        ];
    }

    #[Override]
    protected function expectedController(): string
    {
        return ContentBlockListController::class;
    }

    #[Override]
    protected function captionText(): string
    {
        return 'This table lists Content block entities for the frontend hero section';
    }

    private function getImage(ContentBlock|ContentBlockShow $block, string $key): string
    {
        return match ($key) {
            'url' => $this->presenter->getNestedValue($block, 'block_metadata.image.url'),
            'alt' => $this->presenter->getNestedValue($block, 'block_metadata.image.alt'),
        };
    }

    /** @return ActionDefinition[] */
    private function blockActions(ContentBlock|ContentBlockShow $block): array
    {
        $id = $block->getId();
        return $this->rowActions($block, (string) $id, '/admin/content-block-confirm-deletion/confirm', [], $this->type);
    }

    private function section(ContentBlock|ContentBlockShow $block): string
    {
        return match($block->getBlockType()) {
            BlockType::HERO => 'Hero Section',
            BlockType::SUMMER_BANNER => 'Summer Banner',
            BlockType::BIG_BANNER => 'Big Banner',
            default => '',
        };
    }

    private function displayName(string $type): string
    {
        return match(BlockType::tryFrom($type)) {
            BlockType::HERO => 'Hero Section',
            BlockType::SUMMER_BANNER => 'Summer Banner',
            blockType::BIG_BANNER => 'Big Banner',
            default => 'Content Block',
        };
    }
}