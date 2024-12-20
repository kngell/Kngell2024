<?php

declare(strict_types=1);

abstract class AbstractLiasse
{
    protected array $contenu;

    abstract public function ajouteDocument(string $document): void;

    abstract public function imprime(): string;
}