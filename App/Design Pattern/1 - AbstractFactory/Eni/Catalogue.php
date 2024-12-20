<?php

declare(strict_types=1);

class Catalogue
{
    protected FabriqueVehiculeInterface $fabriqueVehicule;

    public function __construct(FabriqueVehiculeInterface $fabriqueVehicule)
    {
        $this->fabriqueVehicule = $fabriqueVehicule;
    }

    public function creerScooter(string $marque, string $couleur, int $puissance): AbstractScooter
    {
        return $this->fabriqueVehicule->creerScooter($marque, $couleur, $puissance);
    }

    public function creerAutomobile(string $marque, string $couleur, int $puissance, float $espace): AbstractAutomobile
    {
        return $this->fabriqueVehicule->creerAutomobile($marque, $couleur, $puissance, $espace);
    }
}