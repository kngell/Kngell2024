<?php

declare(strict_types=1);

abstract class AbstractClient
{
    protected array $commandes;

    public function nouvelleCommande(float $montant): string
    {
        $commande = $this->creeCommande($montant);

        if ($commande->valide()) {
            $this->commandes[] = $commande;
            return '<p>' . $commande->paye() . '</p>';
        }
        return '';
    }

    abstract protected function creeCommande(float $montant): AbstractCommande;
}