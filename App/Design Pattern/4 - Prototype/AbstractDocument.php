<?php

declare(strict_types=1);

abstract class AbstractDocument
{
    protected string $contenu;

    public function duplique(): self
    {
        return clone $this;
    }

    public function remplit(string $informations): void
    {
        $this->contenu = $informations;
    }

    abstract public function imprime(): string;

    abstract public function affiche(): string;
}