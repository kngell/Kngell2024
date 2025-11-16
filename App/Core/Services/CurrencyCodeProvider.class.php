<?php

declare(strict_types=1);

use Currency;
use CurrencyCodeProviderInterface;
use CurrencyModel;
use Region;
use RegionContextInterface;
use RegionModel;
use RuntimeException;

final class CurrencyCodeProvider implements CurrencyCodeProviderInterface
{
    private const FALLBACK_CODE = 'EUR';

    public function __construct(
        private RegionContextInterface $regionContext,
        private RegionModel $regionModel,
        private CurrencyModel $currencyModel,
    ) {
    }

    public function getSystemDefaultCurrencyCode(): string
    {
        $regionCode = $this->regionContext->getRegionCode();

        /** @var Region|null $region */
        $region = $this->regionModel->one(['region_code' => $regionCode])->asClass();

        if ($region && $region->getCurrencyId()) {
            try {
                return $this->getCurrencyCode($region->getCurrencyId());
            } catch (RuntimeException) {
            }
        }
        return self::FALLBACK_CODE;
    }

    public function getCurrencyCode(int $currencyId): string
    {
        /** @var Currency|null $currency */
        $currency = $this->currencyModel->one(['currency_id' => $currencyId])->asClass();

        if (!$currency) {
            throw new RuntimeException(
                sprintf('Currency with ID "%d" not found in the database. Core configuration missing.', $currencyId),
            );
        }
        return $currency->getCurrencyCode();
    }
}