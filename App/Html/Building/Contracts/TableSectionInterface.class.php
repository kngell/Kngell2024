<?php

declare(strict_types=1);

interface TableSectionInterface
{
    public function getTableSectionType(): TableListSection;

    /**
     * @return array
     */
    public function getContext(): array;
}