<?php

declare(strict_types=1);

class TestProduct extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

    private int $id = 1;
    private string $name = 'Test';
    private int $quantity;
    private string $slug;
    private float $price;
}