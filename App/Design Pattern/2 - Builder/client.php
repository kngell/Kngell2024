<?php

declare(strict_types=1);

$constructeursLiasse = [
    new ConstructeurLiasseVehiculeHtml(),
    new ConstructeurLiasseVehiculePdf(),
];

foreach ($constructeursLiasse as $constructeurLiasse) {
    $vendeur = new Vendeur($constructeurLiasse);
    $liasse = $vendeur->construit('Ferrandez');
    $liasse->imprime();
}