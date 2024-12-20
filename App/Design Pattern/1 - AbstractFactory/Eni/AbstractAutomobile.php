<?php

declare(strict_types=1);

abstract class AbstractAutomobile
{
    protected string $marque;

    protected string $couleur;

    protected int $puissance;

    protected float $espace;

    public function __construct(string $marque, string $couleur, int $puissance, float $espace)
    {
        $this->marque = $marque;
        $this->couleur = $couleur;
        $this->puissance = $puissance;
        $this->espace = $espace;
    }

    abstract public function afficheCaracteristiques(): void;
}