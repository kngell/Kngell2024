<?php

declare(strict_types=1);

interface TableCellRendererInterface
{
    /**
     * @param mixed       $entity    The entity for this row
     * @param array       $colDef    The column definition from getConfig()
     * @param int         $rowIndex  Row index (0-based)
     * @param HtmlBuilder $builder
     *
     * @return AbstractHtmlComponent
     */
    public function render(
        mixed $entity,
        array $colDef,
        int $rowIndex,
        HtmlBuilder $builder,
    ): AbstractHtmlComponent;
}