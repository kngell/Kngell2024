<?php

declare(strict_types=1);

class FabriqueVehiculeElectricite implements FabriqueVehiculeInterface
{
    public function creerAutomobile(string $marque, string $couleur, int $puissance, float $espace): AbstractAutomobile
    {
        return new AutomobileElectricite($marque, $couleur, $puissance, $espace);
    }

    public function creerScooter(string $marque, string $couleur, int $puissance): AbstractScooter
    {
        return new ScooterElectricite($marque, $couleur, $puissance);
    }
}