<?php

declare(strict_types=1);

abstract class AbstractConstructeurLiasseVehicule
{
    protected AbstractLiasse $liasse;

    public function resultat(): AbstractLiasse
    {
        return $this->liasse;
    }

    abstract public function construitBonDeCommande(string $nomClient): void;

    abstract public function construitDemandeImmatriculation(string $nomDemandeur): void;
}