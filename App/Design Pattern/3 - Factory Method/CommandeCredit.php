<?php

declare(strict_types=1);

class CommandeCredit extends AbstractCommande
{
    public function paye(): string
    {
        $montant = number_format($this->montant, 2, ',', ' ');
        return 'Paiement à crédit de ' . $montant . ' effectué.' . PHP_EOL;
    }

    public function valide(): bool
    {
        return $this->montant >= 1000 && $this->montant <= 5000;
    }
}