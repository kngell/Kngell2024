<?php

declare(strict_types=1);

class ConstructeurLiasseVehiculeHtml extends AbstractConstructeurLiasseVehicule
{
    public function __construct()
    {
        $this->liasse = new LiasseHtml();
    }

    public function construitBonDeCommande(string $nomClient): void
    {
        $document = "<HTML>Bon de commande Client : $nomClient</HTML>";
        $this->liasse->ajouteDocument($document);
    }

    public function construitDemandeImmatriculation(string $nomDemandeur): void
    {
        $document = "<HTML>Demande d'immatriculation - Demandeur : $nomDemandeur</HTML>";
        $this->liasse->ajouteDocument($document);
    }
}