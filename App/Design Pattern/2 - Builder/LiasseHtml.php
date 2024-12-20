<?php

declare(strict_types=1);

class LiasseHtml extends AbstractLiasse
{
    public function ajouteDocument(string $document): void
    {
        if (str_starts_with($document, '<HTML>')) {
            $this->contenu[] = $document;
        }
    }

    public function imprime(): string
    {
        $strLigne = 'Liasse HTML' . PHP_EOL;
        foreach ($this->contenu as $ligne) {
            $strLigne .= '<p>' . $ligne . PHP_EOL . '</p>';
        }
        return $strLigne;
    }
}