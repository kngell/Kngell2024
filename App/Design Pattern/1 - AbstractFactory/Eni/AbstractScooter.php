<?php

declare(strict_types=1);

abstract class AbstractScooter
{
    protected string $marque;

    protected string $couleur;

    protected int $puissance;

    public function __construct(string $marque, string $couleur, int $puissance)
    {
        $this->marque = $marque;
        $this->couleur = $couleur;
        $this->puissance = $puissance;
    }

    abstract public function afficheCaracteristiques(): void;
}