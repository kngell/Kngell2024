<?php

declare(strict_types=1);

class DemandeImmatriculation extends AbstractDocument
{
    public function affiche(): string
    {
        return "Affiche la demande d'immatriculation : $this->contenu" . PHP_EOL;
    }

    public function imprime(): string
    {
        return "Imprime la demande d'immatriculation : $this->contenu" . PHP_EOL;
    }
}