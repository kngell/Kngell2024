<?php

declare(strict_types=1);

class CurrencyModel extends Model
{
    /**
     * @throws PDOException
     * @throws QueryResultException
     *
     * @return Currency[]
     */
    public function findActive(): array
    {
        return $this->all(['is_active' => true])->asClass();
    }

    public function findByCode(string $code): Currency
    {
        return $this->one(['currency_code' => $code])->asClass();
    }
}