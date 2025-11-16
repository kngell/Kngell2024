<?php

declare(strict_types=1);

class TaxClassModel extends Model
{
    /**
     * @return TaxClass[]
     */
    public function getActiveTaxClasses(): array
    {
        return $this->all(['active' => 1])->asClass();
    }
}