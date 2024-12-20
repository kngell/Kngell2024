<?php

declare(strict_types=1);

$fabriques = [
    new FabriqueVehiculeElectricite(),
    new FabriqueVehiculeEssence(),
];

$couleurs = ['noir', 'rouge', 'blanc'];
$marques = ['Suzuki', 'Peugeot', 'BMW'];

$vehicules = [];

foreach ($fabriques as $fabrique) {
    $catalogue = new Catalogue($fabrique);

    $vehicules[] = $catalogue->creerAutomobile(
        $marques[array_rand($marques)],
        $couleurs[array_rand($couleurs)],
        mt_rand(110, 150),
        3.2
    );

    $vehicules[] = $catalogue->creerScooter(
        $marques[array_rand($marques)],
        $couleurs[array_rand($couleurs)],
        mt_rand(15, 90)
    );
}

foreach ($vehicules as $vehicule) {
    $vehicule->afficheCaracteristiques();
}