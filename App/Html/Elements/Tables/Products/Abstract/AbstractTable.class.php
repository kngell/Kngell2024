<?php

declare(strict_types=1);

abstract class AbstractTable
{
    abstract public function getTable(): string;

    /**
     * @return AbstractHtmlComponent[]
     */
    abstract protected function buildTableSections(): array;
}