<?php

declare(strict_types=1);

abstract class AbstractBaseFieldHandler
{
    protected ?string $fieldId = null;

    /**
     * @return null|string
     */
    public function getFieldId(): ?string
    {
        return $this->fieldId;
    }
}