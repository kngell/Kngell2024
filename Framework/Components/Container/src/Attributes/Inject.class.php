<?php

declare(strict_types=1);

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_METHOD)]
class Inject
{
    public function __construct(private ?string $id = null)
    {
    }

    public function getId(): ?string
    {
        return $this->id;
    }
}