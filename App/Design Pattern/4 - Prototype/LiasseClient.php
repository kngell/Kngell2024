<?php

declare(strict_types=1);

class LiasseClient extends AbstractLiasseDocument
{
    public function __construct(string $informations)
    {
        $liasseVierge = LiasseVierge::getInstance();

        foreach ($liasseVierge->getDocuments() as $document) {
            $copieDocument = $document->duplique();
            $copieDocument->remplit($informations);
            $this->documents[] = $copieDocument;
        }
    }

    public function affiche(): string
    {
        $doc = '';
        foreach ($this->documents as $document) {
            $doc .= '<p>' . $document->affiche() . '</p>';
        }
        return $doc;
    }

    public function imprime(): string
    {
        $doc = '';
        foreach ($this->documents as $document) {
            $doc .= '<p>' . $document->imprime() . '</p>';
        }
        return $doc;
    }
}