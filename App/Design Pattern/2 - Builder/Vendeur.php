<?php

declare(strict_types=1);

class Vendeur
{
    protected AbstractConstructeurLiasseVehicule $constructeurLiasse;

    public function __construct(AbstractConstructeurLiasseVehicule $constructeurLiasse)
    {
        $this->constructeurLiasse = $constructeurLiasse;
    }

    public function construit(string $nomClient): AbstractLiasse
    {
        $this->constructeurLiasse->construitBonDeCommande($nomClient);
        $this->constructeurLiasse->construitDemandeImmatriculation($nomClient);

        return $this->constructeurLiasse->resultat();
    }
}