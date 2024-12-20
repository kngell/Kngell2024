<?php

declare(strict_types=1);

interface FabriqueVehiculeInterface
{
    public function creerAutomobile(string $marque, string $couleur, int $puissance, float $espace): AbstractAutomobile;

    public function creerScooter(string $marque, string $couleur, int $puissance): AbstractScooter;
}