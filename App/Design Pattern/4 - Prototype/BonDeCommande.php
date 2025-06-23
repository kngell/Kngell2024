<?php

declare(strict_types=1);

class BonDeCommande extends AbstractDocument
{
    public function affiche(): string
    {
        return "Affiche le bon de commande: $this->contenu" . PHP_EOL;
    }

    public function imprime(): string
    {
        return "Imprime le bon de commande: $this->contenu" . PHP_EOL;
    }
}