<?php

declare(strict_types=1);

interface FormSectionConfigBuilderInterface
{
    public function buildMediaConfig(): array;

    public function buildRegularConfig(): array;
}