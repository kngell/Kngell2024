<?php

declare(strict_types=1);

interface FieldSectionInterface extends HtmlSectionInterface
{
    public function getFieldMapping(): array;
}
