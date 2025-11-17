<?php

declare(strict_types=1);

interface DataStandardizerInterface
{
    public function standardize(array $data): array;

    public function setContext(string $context): self;

    public function setInsertMap(array $insertMap): self;
}