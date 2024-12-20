<?php

declare(strict_types=1);

class LiasseVierge extends AbstractLiasseDocument
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function ajoute(AbstractDocument $document): void
    {
        $this->documents[] = $document;
    }

    public function retire(AbstractDocument $document): void
    {
        if (false !== ($index = array_search($document, $this->documents, true))) {
            unset($this->documents[$index]);
        }
    }
}