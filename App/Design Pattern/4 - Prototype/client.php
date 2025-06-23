<?php

declare(strict_types=1);

$liasseVierge = LiasseVierge::getInstance();

$liasseVierge->ajoute(new BonDeCommande());
$liasseVierge->ajoute(new CertificatCession());
$liasseVierge->ajoute(new DemandeImmatriculation());

$liasseClient = new LiasseClient('Georges Durand');
$liasseClient->affiche();
$liasseClient->imprime();