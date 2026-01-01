<?php

declare(strict_types=1);
#[Attribute(Attribute::TARGET_PROPERTY)]
class EntityFieldId
{
    private string|null $name;
    private FieldType|null $type;

    public function __construct(string|null $name = null, FieldType|null $type = FieldType::STRING)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return null|FieldType
     */
    public function getType(): ?FieldType
    {
        return $this->type;
    }
}