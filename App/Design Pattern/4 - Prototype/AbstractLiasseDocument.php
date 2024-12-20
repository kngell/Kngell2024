<?php

declare(strict_types=1);

abstract class AbstractLiasseDocument
{
    protected array $documents = [];

    public function getDocuments(): array
    {
        return $this->documents;
    }
}