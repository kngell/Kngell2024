<?php

declare(strict_types=1);

final class TableBodyRenderer implements TableSectionRendererInterface
{
    use EntityDisplayTrait;

    /** @var array<string, TableCellRendererInterface> */
    private array $cellRenderers = [];

    public function __construct(
        private readonly IconBuilder $icon,
    ) {
        $this->registerCellRenderers();
    }

    public function render(mixed $config, HtmlBuilder $builder): AbstractHtmlComponent
    {
        $columns = $config['columns'] ?? [];
        $entities = $config['entities'] ?? [];

        $rows = array_map(
            fn (mixed $entity, int $index) => $this->buildRow($entity, $columns, $index, $builder),
            $entities,
            array_keys($entities),
        );

        $tbody = $builder->tag('tbody')
            ->class('table__body')
            ->custom(['aria-describedby' => 'table-desc']);

        return !empty($rows)
            ? $tbody->add(...$rows)
            : $tbody;
    }

    private function buildRow(
        Entity $entity,
        array $columns,
        int $rowIndex,
        HtmlBuilder $builder,
    ): AbstractHtmlComponent {
        $cells = [];

        foreach ($columns as $colDef) {
            $cellType = $this->resolveCellType($colDef);
            $renderer = $this->getCellRenderer($cellType);
            $cells[] = $renderer->render($entity, $colDef, $rowIndex, $builder);
        }

        // Build dynamic ID attribute
        $idAttribute = $this->getIdAttribute($entity);
        $entityId = $this->getEntityId($entity);

        return $builder->tag('tr')
            ->class('table__body--row')
            ->attribute($idAttribute, $entityId)
            ->add(...$cells);
    }

    private function getIdAttribute(Entity $entity): string
    {
        $tableName = $entity->table();
        $entityName = $this->getSingularName($tableName);
        return "data-{$entityName}-id";
    }

    private function getSingularName(string $tableName): string
    {
        // Handle special cases
        $specialCases = [
            'heroes' => 'hero',
            'children' => 'child',
            'people' => 'person',
        ];

        if (isset($specialCases[$tableName])) {
            return $specialCases[$tableName];
        }

        if (str_ends_with($tableName, 'ies')) {
            return substr($tableName, 0, -3) . 'y';
        }

        if (str_ends_with($tableName, 's')) {
            return substr($tableName, 0, -1);
        }

        return $tableName;
    }

    /**
     * Resolve cellType from column definition.
     *
     * Accepts:
     *   - TableCellType enum instance (preferred)
     *   - String value matching TableCellType cases ('start', 'normal', etc.)
     *
     * @throws InvalidArgumentException if cellType is invalid
     */
    private function resolveCellType(array $colDef): TableCellType
    {
        $cellType = $colDef['cellType'] ?? TableCellType::NORMAL;

        if ($cellType instanceof TableCellType) {
            return $cellType;
        }

        // Handle legacy string values or misconfiguration
        if (is_string($cellType)) {
            return TableCellType::tryFrom($cellType)
                ?? throw new InvalidArgumentException(
                    sprintf(
                        'Invalid cellType "%s" for column "%s". Valid types: [%s]',
                        $cellType,
                        $colDef['key'] ?? 'unknown',
                        implode(', ', array_column(TableCellType::cases(), 'value')),
                    ),
                );
        }

        throw new InvalidArgumentException(
            sprintf(
                'cellType must be a TableCellType enum or string, got %s',
                get_debug_type($cellType),
            ),
        );
    }

    /**
     * Get the renderer for a given cell type.
     *
     * @throws LogicException if no renderer is registered for the type
     */
    private function getCellRenderer(TableCellType $cellType): TableCellRendererInterface
    {
        $key = $cellType->value;

        if (!isset($this->cellRenderers[$key])) {
            throw new LogicException(
                sprintf(
                    'No cell renderer registered for cellType "%s". Available: [%s]',
                    $key,
                    implode(', ', array_keys($this->cellRenderers)),
                ),
            );
        }

        return $this->cellRenderers[$key];
    }

    /**
     * Register cell renderers keyed by TableCellType values.
     *
     * This mapping is the single source of truth connecting:
     *   - TableCellType enum
     *   - Cell renderer classes
     *   - SCSS .table__cell--{type} classes
     */
    private function registerCellRenderers(): void
    {
        $this->cellRenderers = [
            TableCellType::START->value => new RowHeaderCellRenderer(),
            TableCellType::NORMAL->value => new NormalCellRenderer(),
            TableCellType::BADGE->value => new BadgeCellRenderer(),
            TableCellType::ACTION->value => new ActionCellRenderer($this->icon),
        ];
    }
}