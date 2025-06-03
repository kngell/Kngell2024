<?php

declare(strict_types=1);

use Brick\Money\Context\AutoContext;
use Brick\Money\Money;

class MoneyManager
{
    private const string CURRENCY = 'EUR';
    private static $instance = null;

    public static function getInstance() : static
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function convertToEuro(int|float|string|null $price) : string
    {
        return Money::of($price, self::CURRENCY, new AutoContext())->formatTo('fr_FR');
    }
}