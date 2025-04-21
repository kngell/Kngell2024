<?php

declare(strict_types=1);

class SearchType extends AbstractInput
{
    private const string TYPE = 'search';

    public function generate(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}