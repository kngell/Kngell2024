<?php

declare(strict_types=1);

interface NumberCollectionInterface
{
    public function addNum(mixed $num): self;

    public function num();

    public function has(): bool;

    public function type(): string;

    public function add(mixed $num);

    public function sub(mixed $num);

    public function divi(mixed $num);

    public function times(mixed $num);

    public function perc(mixed $percentage, bool $reverse = false);

    public function frac();

    public function format(float $number, int $decimal = 2, ?string $sep = '.');

    public function numeric();

    public function range(mixed $to): static;
}
