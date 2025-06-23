<?php

declare(strict_types=1);

class ClientComptant extends AbstractClient
{
    protected function creeCommande(float $montant): AbstractCommande
    {
        return new CommandeComptant($montant);
    }
}