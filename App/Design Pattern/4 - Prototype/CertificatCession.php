<?php

declare(strict_types=1);

class CertificatCession extends AbstractDocument
{
    public function affiche(): string
    {
        return "Affiche le certificat de cession: $this->contenu" . PHP_EOL;
    }

    public function imprime(): string
    {
        return "Imprime le certificat de cession: $this->contenu" . PHP_EOL;
    }
}