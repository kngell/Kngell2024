<?php

declare(strict_types=1);

class CommandeComptant extends AbstractCommande
{
    public function paye(): string
    {
        $montant = number_format($this->montant, 2, ',', ' ');
        return 'Paiement comptant de ' . $montant . ' effectué.' . PHP_EOL;
    }

    public function valide(): bool
    {
        return true;
    }
}