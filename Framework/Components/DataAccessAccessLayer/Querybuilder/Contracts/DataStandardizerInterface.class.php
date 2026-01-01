<?php

declare(strict_types=1);

interface DataStandardizerInterface
{
    public function standardize(array $data): SelectPayload|InsertPayload|UpdatePayload;

    public function setMethod(string $context): self;

    public function setMap(array $insertMap): self;
}