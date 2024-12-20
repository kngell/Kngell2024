<?php

declare(strict_types=1);

class FabriqueVehiculeEssence implements FabriqueVehiculeInterface
{
    public function creerAutomobile(string $marque, string $couleur, int $puissance, float $espace): AbstractAutomobile
    {
        return new AutomobileEssence($marque, $couleur, $puissance, $espace);
    }

    public function creerScooter(string $marque, string $couleur, int $puissance): AbstractScooter
    {
        return new ScooterEssence($marque, $couleur, $puissance);
    }
}