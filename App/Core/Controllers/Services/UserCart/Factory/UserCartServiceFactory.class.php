<?php

declare(strict_types=1);

final class UserCartServiceFactory
{
    public function __construct(
        private readonly RegionContextInterface $regionContext,
        private readonly CurrencyCodeProviderInterface $currencyCodeProvider,
        private readonly SessionInterface $session,
    ) {
    }

    public function create(): UserCartItemService
    {
        $moneyManager = new MoneyManager(
            regionContext: $this->regionContext,
            currencyCodeProvider: $this->currencyCodeProvider,
        );

        $taxManager = new TaxManager(
            regionContext: $this->regionContext,
            moneyManager: $moneyManager,
        );

        return new UserCartItemService(
            session: $this->session,
            moneyManager: $moneyManager,
            taxManager: $taxManager,
        );
    }
}