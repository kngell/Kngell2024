<?php

declare(strict_types=1);

class ClientCredit extends AbstractClient
{
    protected function creeCommande(float $montant): AbstractCommande
    {
        return new CommandeCredit($montant);
    }
}