<?php

// EntityFieldId.php
declare(strict_types=1);

#[Attribute(Attribute::TARGET_PROPERTY)]
class EntityFieldId
{
    public function __construct(
        private string|null $name = null,
        private FieldType|null $type = FieldType::INT,
        private bool $obfuscate = false,  // Whether to obfuscate this ID for public display
        private string|null $obfuscationStrategy = 'hashid', // hashid, encrypt, or null
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getType(): ?FieldType
    {
        return $this->type;
    }

    public function shouldObfuscate(): bool
    {
        return $this->obfuscate;
    }

    public function getObfuscationStrategy(): ?string
    {
        return $this->obfuscationStrategy;
    }
}