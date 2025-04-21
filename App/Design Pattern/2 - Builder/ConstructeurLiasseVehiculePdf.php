<?php

declare(strict_types=1);

class ConstructeurLiasseVehiculePdf extends AbstractConstructeurLiasseVehicule
{
    public function __construct()
    {
        $this->liasse = new LiassePdf();
    }

    public function construitBonDeCommande(string $nomClient): void
    {
        $document = "<PDF>Bon de commande Client : $nomClient</PDF>";
        $this->liasse->ajouteDocument($document);
    }

    public function construitDemandeImmatriculation(string $nomDemandeur): void
    {
        $document = "<PDF>Demande d'immatriculation - Demandeur : $nomDemandeur</PDF>";
        $this->liasse->ajouteDocument($document);
    }
}