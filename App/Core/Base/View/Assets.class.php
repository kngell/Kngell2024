<?php

declare(strict_types=1);
readonly class Assets
{
    public function __construct(
        #[Property(name: 'kngell-ecom.assetsPath')]
        private array $assets
    ) {
    }
}
