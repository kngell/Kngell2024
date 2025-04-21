<?php

declare(strict_types=1);

abstract class AbstractCommande
{
    protected float $montant;

    public function __construct(float $montant)
    {
        $this->montant = $montant;
    }

    abstract public function valide(): bool;

    abstract public function paye(): string;
}