<?php

declare(strict_types=1);

final class TableHeadRenderer implements TableSectionRendererInterface
{
    public function __construct(
        private readonly HtmlBuilder $builder,
        private readonly IconBuilder $icon,
    ) {
    }

    public function render(mixed $config, HtmlBuilder $builder): AbstractHtmlComponent
    {
        $columns = $this->resolveColumns($config);

        $headerCells = array_map(
            fn (TableColumnConfig $col) => $this->buildHeaderCell($col),
            $columns,
        );

        return $builder->tag('thead')->class('table__head')->add(
            $builder->tag('tr')->class('table__head--row')->add(
                ...$headerCells,
            ),
        );
    }

    // ──────────────────────────────────────────────────────────────────
    // Cell building
    // ──────────────────────────────────────────────────────────────────

    /**
     * Build a single header cell.
     *
     * The cellType determines the CSS class (table__cell--{type}).
     * The config flags determine the internal structure.
     */
    private function buildHeaderCell(TableColumnConfig $col): AbstractHtmlComponent
    {
        $isRichHeader = $col->hasCheckbox || $col->hasDropdown || !empty($col->hintText);

        $content = $isRichHeader
            ? $this->buildRichHeader($col)
            : $this->buildSimpleHeader($col);

        return $this->wrapInTh($col, $content);
    }

    /**
     * Simple header: just a text label.
     *
     * Used for: SKU, Category, Action (no dropdown, no checkbox)
     * Output: <span class="header-cell">Label</span>
     */
    private function buildSimpleHeader(TableColumnConfig $col): AbstractHtmlComponent
    {
        return $this->builder->tag('span')
            ->class('header-cell')
            ->content($col->label);
    }

    /**
     * Rich header: label with optional checkbox, dropdown, and hint text.
     *
     * Used for: Product (checkbox + dropdown), Stock/Price/Status/Added (dropdown)
     * Output:
     *   <div class="header-cell">
     *     <div class="header-cell__top-row">
     *       [checkbox-group | label-span]
     *       [dropdown-button]
     *     </div>
     *     <span class="header-cell__hint-text">...</span>
     *   </div>
     */
    private function buildRichHeader(TableColumnConfig $col): AbstractHtmlComponent
    {
        $html = $this->builder;
        $topRowChildren = [];

        // Primary content: checkbox group or plain label
        $topRowChildren[] = $col->hasCheckbox
            ? $this->buildCheckboxGroup($col)
            : $html->tag('span')->content($col->label);

        // Optional dropdown
        if ($col->hasDropdown) {
            $topRowChildren[] = $this->buildDropdown($col->key);
        }

        return $html->tag('div')->class('header-cell')->add(
            $html->tag('div')->class('header-cell__top-row')->add(
                ...$topRowChildren,
            ),
            $html->tag('span')
                ->class('header-cell__hint-text')
                ->content($col->hintText),
        );
    }

    // ──────────────────────────────────────────────────────────────────
    // Sub-components
    // ──────────────────────────────────────────────────────────────────

    private function buildCheckboxGroup(TableColumnConfig $col): AbstractHtmlComponent
    {
        $html = $this->builder;
        $checkboxId = "select-all-{$col->key}";

        return $html->tag('div')->class('checkbox-box')->add(
            $html->input('checkbox')
                ->id($checkboxId)
                ->class('checkbox-box__input')
                ->custom([
                    'aria-label' => $col->ariaLabel ?: "Select all {$col->label}",
                ]),
            $html->label($col->label)
                ->for($checkboxId)
                ->class('checkbox-box__label'),
        );
    }

    private function buildDropdown(string $columnKey): AbstractHtmlComponent
    {
        $html = $this->builder;
        $dropdownId = "dropdown-{$columnKey}";

        return $html->tag('div')->class('dropdown-container')->add(
            $html->button('button')
                ->class('dropdown-container__btn')
                ->custom([
                    'aria-expanded' => 'false',
                    'aria-controls' => $dropdownId,
                ])
                ->add(
                    $this->icon->createIcon('icon-arrow-down', 'dropdown', ['arrow-down']),
                ),
            $html->tag('div')
                ->id($dropdownId)
                ->class('dropdown-container__dropdown')
                ->hidden(true),
        );
    }

    // ──────────────────────────────────────────────────────────────────
    // Wrapper
    // ──────────────────────────────────────────────────────────────────

    /**
     * Wrap content in <th> with base class + cell-type class.
     *
     * Output: <th scope="col"
     *             class="table__head--row-cell table__cell--{cellType}"
     *             [aria-label="..."]
     *             [aria-sort="none"]>
     */
    private function wrapInTh(
        TableColumnConfig $col,
        AbstractHtmlComponent $content,
    ): AbstractHtmlComponent {
        $th = $this->builder->tag('th')
            ->custom(['scope' => 'col'])
            ->class('table__head--row-cell', $col->getCellTypeClass());

        if (!empty($col->ariaLabel)) {
            $th = $th->custom(['aria-label' => $col->ariaLabel]);
        }

        if ($col->sortable) {
            $th = $th->custom(['aria-sort' => 'none']);
        }

        return $th->add($content);
    }

    // ──────────────────────────────────────────────────────────────────
    // Resolution
    // ──────────────────────────────────────────────────────────────────

    /**
     * @return TableColumnConfig[]
     */
    private function resolveColumns(array $config): array
    {
        return array_map(
            fn (mixed $col) => $col instanceof TableColumnConfig
                ? $col
                : TableColumnConfig::fromArray($col),
            $config,
        );
    }
}