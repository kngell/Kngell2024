<?php

declare(strict_types=1);

/**
 * Defines the 4 visual cell types used across the entire table system.
 *
 * Used by:
 *   - Column config:   TableColumnConfig::cellType
 *   - Head renderer:   determines <th> class + internal structure
 *   - Body renderer:   maps to cell renderer + <td>/<th> class
 *   - SCSS:            .table__cell--{value} + .table__col--{value}
 *   - HTML <col>:      <col class="table__col--{value}">
 *
 * Head rendering behavior per type:
 *   start  → checkbox group + optional dropdown + hint text
 *   normal → plain label OR label + dropdown (based on hasDropdown flag)
 *   badge  → plain label OR label + dropdown (same as normal visually in head)
 *   action → plain label only
 */
enum TableCellType: string
{
    /**
     * Returns the BEM modifier suffix for CSS classes.
     * Used to generate: table__cell--start, table__col--start, etc.
     */
    public function cssClass(): string
    {
        return $this->value;
    }

    /**
     * Whether this cell type renders as <th scope="row"> in the body.
     * Only 'start' uses a row header element.
     */
    public function isRowHeader(): bool
    {
        return $this === self::START;
    }

    /**
     * Whether this cell type supports a checkbox in the header.
     */
    public function supportsHeaderCheckbox(): bool
    {
        return $this === self::START;
    }
    case START = 'start';
    case NORMAL = 'normal';
    case BADGE = 'badge';
    case ACTION = 'action';
}