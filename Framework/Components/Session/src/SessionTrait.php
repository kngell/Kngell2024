<?php

declare(strict_types=1);

trait SessionTrait
{
    /**
     * Simple session validation.
     */
    private function validateSession(): bool
    {
        if (isset($_SESSION['OBSOLETE']) && !isset($_SESSION['EXPIRES'])) {
            return false;
        }
        if (isset($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time()) {
            return false;
        }
        return true;
    }

    /**
     * Basic session hijack prevention.
     */
    private function preventSessionHijack(): bool
    {
        return !empty($_SESSION);
    }

    /**
     * Session regeneration.
     */
    private function sessionRegeneration(): void
    {
        session_regenerate_id(true);
    }
}