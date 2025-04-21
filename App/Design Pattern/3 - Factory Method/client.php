<?php

declare(strict_types=1);

$client = new ClientComptant();
$client->nouvelleCommande(2000);
$client->nouvelleCommande(10000);

$client = new ClientCredit();
$client->nouvelleCommande(2199.99);
$client->nouvelleCommande(10000);