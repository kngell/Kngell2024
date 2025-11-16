<?php

declare(strict_types=1);

interface VariationBuilderInterface
{
    public function buildVariationGroups(bool $isEdit, $formValues): array;

    public function getBaseVariationGroup(): array;
}